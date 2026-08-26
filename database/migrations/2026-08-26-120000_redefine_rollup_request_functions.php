<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Redefine the request rollup functions.
     *
     * The four functions were created by 2025-03-04-000200_analytics without a
     * column list on their INSERT ... SELECT. The rollup tables declare
     * object_uid before model_class while the SELECT produces model_class
     * first, so Postgres mapped them positionally and every aggregated row had
     * the two transposed -- model_class held object uids. Every filter that
     * compares model_class therefore matched nothing.
     *
     * That migration has already run everywhere, and Laravel never re-runs a
     * migration, so correcting it in place only helps a fresh install. This
     * migration exists to carry the same correction to databases that already
     * have the broken definitions.
     *
     * CREATE OR REPLACE touches no data and no bookmarks, so this is a no-op
     * on a database created after the fix, and safe to run more than once.
     *
     * It deliberately does not repair rows already aggregated by the broken
     * functions -- those have the columns transposed and there is no in-place
     * fix for them. Truncating the rollup tables and resetting
     * last_aggregated_id in {prefix}rollups rebuilds them correctly; see the
     * upgrade notes.
     */
    public function up(): void
    {
        $prefix = config('stickle.database.tablePrefix');

        DB::connection()->getPdo()->exec("
-- ----------------------------------------------------------------------------
-- REQUESTS 1 MINUTE AGGREGATION
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION {$prefix}rollup_requests_1min(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which requests we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM {$prefix}incremental_rollup_window('{$prefix}requests_rollup_1min');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the requests, merge results if the entry already exists */
    /* The column list is required, not decorative: the table declares
       object_uid before model_class, so an unqualified INSERT maps this
       SELECT positionally and silently transposes the two. */
    INSERT INTO {$prefix}requests_rollup_1min
        (model_class, object_uid, type, name, title, path, url, minute, request_count)
        SELECT  model_class,
                object_uid,
                type,
                properties->>'name' as name,
                properties->>'title' as title,
                properties->>'path' as path,
                properties->>'url' as url,
                date_trunc('seconds', (timestamp - TIMESTAMP 'epoch') / 60) * 60 + TIMESTAMP 'epoch' AS minute,
                count(*) as request_count
        FROM {$prefix}requests WHERE {$prefix}requests.id BETWEEN start_id AND end_id
        GROUP BY model_class, object_uid, type, name, title, path, url, minute
        ON CONFLICT (model_class, object_uid, type, name, title, path, url, minute)
        DO UPDATE
        SET request_count = {$prefix}requests_rollup_1min.request_count + excluded.request_count;
END; 
$$
LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- REQUESTS 5 MINUTE AGGREGATION
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION {$prefix}rollup_requests_5min(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which requests we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM {$prefix}incremental_rollup_window('{$prefix}requests_rollup_5min');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the requests, merge results if the entry already exists */
    /* The column list is required, not decorative: the table declares
       object_uid before model_class, so an unqualified INSERT maps this
       SELECT positionally and silently transposes the two. */
    INSERT INTO {$prefix}requests_rollup_5min
        (model_class, object_uid, type, name, title, path, url, minute, request_count)
        SELECT  model_class,
                object_uid,
                type,
                properties->>'name' as name,
                properties->>'title' as title,
                properties->>'path' as path,
                properties->>'url' as url,
                date_trunc('seconds', (timestamp - TIMESTAMP 'epoch') / 300) * 300 + TIMESTAMP 'epoch' AS minute,
                count(*) as request_count
        FROM {$prefix}requests WHERE {$prefix}requests.id BETWEEN start_id AND end_id
        GROUP BY model_class, object_uid, type, name, title, path, url, minute
        ON CONFLICT (model_class, object_uid, type, name, title, path, url, minute)
        DO UPDATE
        SET request_count = {$prefix}requests_rollup_5min.request_count + excluded.request_count;
END; 
$$
LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- REQUESTS 1 HOUR AGGREGATION
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION {$prefix}rollup_requests_1hr(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which requests we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM {$prefix}incremental_rollup_window('{$prefix}requests_rollup_1hr');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the requests, merge results if the entry already exists */
    /* The column list is required, not decorative: the table declares
       object_uid before model_class, so an unqualified INSERT maps this
       SELECT positionally and silently transposes the two. */
    INSERT INTO {$prefix}requests_rollup_1hr
        (model_class, object_uid, type, name, title, path, url, hour, request_count)
        SELECT  model_class,
                object_uid,
                type,
                properties->>'name' as name,
                properties->>'title' as title,
                properties->>'path' as path,
                properties->>'url' as url,
                date_trunc('hour', timestamp) as hour,
                count(*) as request_count
        FROM {$prefix}requests WHERE {$prefix}requests.id BETWEEN start_id AND end_id
        GROUP BY model_class, object_uid, type, name, title, path, url, hour
        ON CONFLICT (model_class, object_uid, type, name, title, path, url, hour)
        DO UPDATE
        SET request_count = {$prefix}requests_rollup_1hr.request_count + excluded.request_count;
END; 
$$
LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- REQUESTS 1 DAY AGGREGATION
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION {$prefix}rollup_requests_1day(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which requests we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM {$prefix}incremental_rollup_window('{$prefix}requests_rollup_1day');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the requests, merge results if the entry already exists */
    /* The column list is required, not decorative: the table declares
       object_uid before model_class, so an unqualified INSERT maps this
       SELECT positionally and silently transposes the two. */
    INSERT INTO {$prefix}requests_rollup_1day
        (model_class, object_uid, type, name, title, path, url, day, request_count)
        SELECT  model_class,
                object_uid,
                type,
                properties->>'name' as name,
                properties->>'title' as title,
                properties->>'path' as path,
                properties->>'url' as url,
                date_trunc('day', timestamp) as day,
                count(*) as request_count
        FROM {$prefix}requests WHERE {$prefix}requests.id BETWEEN start_id AND end_id
        GROUP BY model_class, object_uid, type, name, title, path, url, day
        ON CONFLICT (model_class, object_uid, type, name, title, path, url, day)
        DO UPDATE
        SET request_count = {$prefix}requests_rollup_1day.request_count + excluded.request_count;
END; 
$$
LANGUAGE plpgsql;
");
    }

    /**
     * Irreversible by choice. Rolling back would mean restoring definitions
     * that silently transpose two columns, which is not a state worth being
     * able to return to.
     */
    public function down(): void {}
};
