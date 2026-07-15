<?php

declare(strict_types=1);

namespace StickleApp\Core\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Contracts\SegmentContract;

/**
 * Reconciles a segment's membership entirely inside PostgreSQL.
 *
 * This is the default replacement for the CSV export/import pair
 * (ExportSegmentAction + ImportSegmentAction), which round-tripped the segment
 * query out of the database into a CSV and back in via a `psql \copy`
 * shell-out. Both ends of that round trip are the same database, so the file
 * bought nothing and cost a shared disk, a psql binary, and a silent failure
 * mode. See config('stickle.segments.useCsvExports') for the legacy path.
 *
 * The statement below preserves the two properties the CSV design existed to
 * protect:
 *
 *  - **Materialization boundary.** `src` is a MATERIALIZED CTE, so the segment
 *    query is evaluated once, up front, taking only ACCESS SHARE on the source
 *    tables and holding no write lock on model_segment while it runs. The
 *    membership write happens afterwards, against the materialized set. This is
 *    the same read-then-merge split the temp table provided — it is what keeps
 *    a long segment query from holding a write lock on model_segment for its
 *    whole duration.
 *  - **created_at preservation.** `ON CONFLICT DO NOTHING` leaves existing rows
 *    untouched, so a member's original entry timestamp survives re-runs. Only
 *    genuinely new members are inserted, which also keeps the audit trigger
 *    from emitting spurious ENTER/EXIT events on every sync.
 *
 * `ORDER BY object_uid` + `FOR UPDATE` make every concurrent sync acquire row
 * locks in the same order, so two overlapping runs of the same segment cannot
 * form a deadlock cycle. (ExportSegmentJob's WithoutOverlapping lock expires
 * after $uniqueFor seconds, so a slow sync can legitimately overlap its own
 * next run.) SKIP LOCKED is deliberately NOT used: it would silently drop
 * contended rows from the reconciliation to buy protection against a deadlock
 * that consistent ordering already prevents.
 */
class SyncSegmentAction
{
    public function __invoke(
        int $segmentId,
        SegmentContract $segmentContract,
    ): void {
        Log::debug(self::class, ['segment_id' => $segmentId]);

        $builder = $segmentContract->toBuilder();
        $model = $builder->getModel();

        // object_uid is a text column; cast explicitly so the join in the
        // reconciliation below does not hit a bigint/text type mismatch.
        $builder->selectRaw(
            $model->getTable().'.'.$model->getKeyName().'::text as object_uid'
        );

        /** @var string $prefix */
        $prefix = config('stickle.database.tablePrefix');

        $table = $prefix.'model_segment';

        $sql = <<<SQL
        WITH src AS MATERIALIZED (
            SELECT DISTINCT sub.object_uid FROM ({$builder->toSql()}) sub
        ),
        stale AS (
            SELECT      ms.object_uid
            FROM        {$table} ms
            WHERE       ms.segment_id = ?
            AND         NOT EXISTS (
                            SELECT 1 FROM src WHERE src.object_uid = ms.object_uid
                        )
            ORDER BY    ms.object_uid
            FOR UPDATE
        ),
        removed AS (
            DELETE FROM {$table} ms
            WHERE       ms.segment_id = ?
            AND         ms.object_uid IN (SELECT object_uid FROM stale)
        )
        INSERT INTO     {$table} (object_uid, segment_id, created_at, updated_at)
        SELECT          src.object_uid, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
        FROM            src
        ORDER BY        src.object_uid
        ON CONFLICT     (object_uid, segment_id) DO NOTHING;
        SQL;

        DB::statement($sql, [
            ...$builder->getBindings(),
            $segmentId,
            $segmentId,
            $segmentId,
        ]);
    }
}
