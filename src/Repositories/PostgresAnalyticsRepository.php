<?php

declare(strict_types=1);

namespace StickleApp\Core\Repositories;

use DateTimeInterface;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;

/**
 * @internal
 */
final class PostgresAnalyticsRepository implements AnalyticsRepositoryContract
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix = null,
    ) {}

    /**
     * Save a request
     * 
     * @param array<string, mixed> $properties
     */
    public function saveRequest(
        string $type,
        string $modelClass,
        string $objectUid,
        string $sessionUid,
        string $ipAddress,
        DateTimeInterface $timestamp,
        ?array $properties = [],
    ): void {
        DB::table($this->prefix.'requests')->insert([
            'type' => $type,
            'model_class' => $modelClass,
            'object_uid' => $objectUid,
            'session_uid' => $sessionUid,
            'ip_address' => $ipAddress,
            'properties' => json_encode($properties),
            'timestamp' => $timestamp,
        ]);
    }

    public function rollupSessions(DateTimeInterface $startDate): void
    {
        $sql = <<<sql
INSERT INTO {$this->prefix}sessions_rollup_1day (
    model_class, 
    object_uid, 
    day, 
    session_count
)
    WITH first_session_events AS (
        SELECT
            model_class,
            object_uid,
            session_uid,
            MIN(DATE(timestamp)) AS first_day
        FROM
            {$this->prefix}requests 
        WHERE 
            offline = FALSE AND timestamp > '%s'
        GROUP BY
            model_class,
            object_uid,
            session_uid
    )
    SELECT
        model_class,
        object_uid,
        first_day AS day,
        COUNT(DISTINCT session_uid) AS session_count
    FROM
        first_session_events
    GROUP BY
        model_class,
        object_uid,
        first_day
ON CONFLICT (model_class, object_uid, day) DO UPDATE SET session_count = EXCLUDED.session_count;
sql;

        \DB::statement(sprintf($sql, $startDate->format('Y-m-d'), $startDate->format('Y-m-d')));
    }

    public function saveEvent(
        string $model,
        string $objectUid,
        string $sessionUid,
        DateTimeInterface $timestamp,
        string $event,
    ): void {
        DB::table($this->prefix.'requests')->insert([
            'activity_type' => 'event',
            'model_class' => $model,
            'object_uid' => $objectUid,
            'session_uid' => $sessionUid,
            'name' => $event,
            'timestamp' => $timestamp,
        ]);
    }
}
