<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var string
     */
    private $prefix = 'lc_';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->prefix;

        Schema::create("{$prefix}rollups", function (Blueprint $table) {
            $table->text('name')->nullable(false);
            $table->text('table_name')->nullable(false);
            $table->text('id_sequence_name')->nullable(false);
            $table->bigInteger(('last_aggregated_id'))->default(0);
        });

        // events_rollup_1min
        \DB::connection()->getPdo()->exec("
-- ----------------------------------------------------------------------------
-- SETUP ROLLUP
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION {$prefix}incremental_rollup_window(rollup_name text, OUT window_start bigint, OUT window_end bigint)
RETURNS record
AS 
$$
DECLARE
    table_to_lock regclass;
BEGIN
    /*
    * Perform aggregation from the last aggregated ID + 1 up to the last committed ID.
    * We do a SELECT .. FOR UPDATE on the row in the rollup table to prevent
    * aggregations from running concurrently.
    */
    SELECT table_name, last_aggregated_id + 1, pg_sequence_last_value(id_sequence_name)
    INTO table_to_lock, window_start, window_end
    FROM {$prefix}rollups
    WHERE name = rollup_name FOR UPDATE;

    IF NOT FOUND THEN
        RAISE 'rollup ''%'' is not in the rollups table', rollup_name;
    END IF;

    IF window_end IS NULL THEN
        /* sequence was never used */
        window_end := 0;
        RETURN;
    END IF;

    /*
    * Play a little trick: We very briefly lock the table for writes in order to
    * wait for all pending writes to finish. That way, we are sure that there are
    * no more uncommitted writes with a identifier lower or equal to window_end.
    * By throwing an exception, we release the lock immediately after obtaining it
    * such that writes can resume.
    */
    BEGIN
        EXECUTE format('LOCK %s IN EXCLUSIVE MODE', table_to_lock);
        RAISE 'release table lock';
    EXCEPTION WHEN OTHERS THEN
    END;

    /*
    * Remember the end of the window to continue from there next time.
    */
    UPDATE {$prefix}rollups SET last_aggregated_id = window_end WHERE name = rollup_name;
END;
$$
LANGUAGE plpgsql;

INSERT INTO {$prefix}rollups (name, table_name, id_sequence_name)
VALUES ('{$prefix}events_rollup_1min', '{$prefix}events', '{$prefix}events_id_seq');

INSERT INTO {$prefix}rollups (name, table_name, id_sequence_name)
VALUES ('{$prefix}events_rollup_5min', '{$prefix}events', '{$prefix}events_id_seq');

INSERT INTO {$prefix}rollups (name, table_name, id_sequence_name)
VALUES ('{$prefix}events_rollup_1hr', '{$prefix}events', '{$prefix}events_id_seq');

INSERT INTO {$prefix}rollups (name, table_name, id_sequence_name)
VALUES ('{$prefix}events_rollup_1day', '{$prefix}events', '{$prefix}events_id_seq');

CREATE TABLE {$prefix}events (
    id BIGSERIAL,
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    session_uid TEXT NULL,
    event_name TEXT NOT NULL,
    properties JSONB NULL,
    timestamp TIMESTAMPTZ DEFAULT NOW() NOT NULL
) PARTITION BY RANGE (timestamp);
CREATE INDEX ON public.{$prefix}events (timestamp);

CREATE TABLE {$prefix}events_rollup_1min (
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    event_name TEXT NOT NULL,
    minute TIMESTAMPTZ NOT NULL,
    event_count bigint
) PARTITION BY RANGE (minute);
CREATE INDEX ON {$prefix}events_rollup_1min (minute);
CREATE UNIQUE INDEX {$prefix}events_rollup_1min_unique_idx ON {$prefix}events_rollup_1min(object_uid, model, event_name, minute);

CREATE TABLE {$prefix}events_rollup_5min (
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    event_name TEXT NOT NULL,
    minute TIMESTAMPTZ NOT NULL,
    event_count bigint
) PARTITION BY RANGE (minute);
CREATE INDEX ON {$prefix}events_rollup_5min (minute);
CREATE UNIQUE INDEX {$prefix}events_rollup_5min_unique_idx ON {$prefix}events_rollup_5min(object_uid, model, event_name, minute);

CREATE TABLE {$prefix}events_rollup_1hr (
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    event_name TEXT NOT NULL,
    hour TIMESTAMPTZ NOT NULL,
    event_count bigint
) PARTITION BY RANGE (hour);
CREATE INDEX ON {$prefix}events_rollup_1hr (hour);
CREATE UNIQUE INDEX {$prefix}events_rollup_1hr_unique_idx ON {$prefix}events_rollup_1hr(object_uid, model, event_name, hour);

CREATE TABLE {$prefix}events_rollup_1day (
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    event_name TEXT NOT NULL,
    day TIMESTAMPTZ NOT NULL,
    event_count bigint
) PARTITION BY RANGE (day);
CREATE INDEX ON {$prefix}events_rollup_1day (day);
CREATE UNIQUE INDEX {$prefix}events_rollup_1day_unique_idx ON {$prefix}events_rollup_1day(object_uid, model, event_name, day);

-- ----------------------------------------------------------------------------
-- EVENTS 1 MINUTE AGGREGATION
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION {$prefix}rollup_events_1min(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which events we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM {$prefix}incremental_rollup_window('{$prefix}events_rollup_1min');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the events, merge results if the entry already exists */
    INSERT INTO {$prefix}events_rollup_1min
        SELECT  object_uid,
                model,
                event_name,
                date_trunc('seconds', (timestamp - TIMESTAMP 'epoch') / 60) * 60 + TIMESTAMP 'epoch' AS minute,
                count(*) as event_count
        FROM {$prefix}events WHERE {$prefix}events.id BETWEEN start_id AND end_id
        GROUP BY object_uid, model, event_name, minute
        ON CONFLICT (object_uid, model, event_name, minute)
        DO UPDATE
        SET event_count = {$prefix}events_rollup_1min.event_count + excluded.event_count;
END; 
$$
LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- EVENTS 5 MINUTE AGGREGATION
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION {$prefix}rollup_events_5min(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which events we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM {$prefix}incremental_rollup_window('{$prefix}events_rollup_5min');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the events, merge results if the entry already exists */
    INSERT INTO {$prefix}events_rollup_5min
        SELECT  object_uid,
                model,
                event_name,
                date_trunc('seconds', (timestamp - TIMESTAMP 'epoch') / 300) * 300 + TIMESTAMP 'epoch' AS minute,
                count(*) as event_count
        FROM {$prefix}events WHERE {$prefix}events.id BETWEEN start_id AND end_id
        GROUP BY object_uid, model, event_name, minute
        ON CONFLICT (object_uid, model, event_name, minute)
        DO UPDATE
        SET event_count = {$prefix}events_rollup_5min.event_count + excluded.event_count;
END; 
$$
LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- EVENTS 1 HOUR AGGREGATION
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION {$prefix}rollup_events_1hr(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which events we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM {$prefix}incremental_rollup_window('{$prefix}events_rollup_1hr');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the events, merge results if the entry already exists */
    INSERT INTO {$prefix}events_rollup_1hr
        SELECT  object_uid,
                model,
                event_name,
                date_trunc('hour', timestamp) as hour,
                count(*) as event_count
        FROM {$prefix}events WHERE {$prefix}events.id BETWEEN start_id AND end_id
        GROUP BY object_uid, model, event_name, hour
        ON CONFLICT (object_uid, model, event_name, hour)
        DO UPDATE
        SET event_count = {$prefix}events_rollup_1hr.event_count + excluded.event_count;
END;
$$
LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- EVENTS 1 DAY AGGREGATION
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION {$prefix}rollup_events_1day(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which events we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM {$prefix}incremental_rollup_window('{$prefix}events_rollup_1day');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the events, merge results if the entry already exists */
    INSERT INTO {$prefix}events_rollup_1day
        SELECT  object_uid,
                model,
                event_name,
                date_trunc('day', timestamp) as day,
                count(*) as event_count
        FROM {$prefix}events WHERE id BETWEEN start_id AND end_id
        GROUP BY object_uid, model, event_name, day
        ON CONFLICT (object_uid, model, event_name, day)
        DO UPDATE
        SET event_count = {$prefix}events_rollup_1day.event_count + excluded.event_count;
END;
$$
LANGUAGE plpgsql;

INSERT INTO {$prefix}rollups (name, table_name, id_sequence_name)
VALUES ('{$prefix}requests_rollup_1min', '{$prefix}requests', '{$prefix}requests_id_seq');

INSERT INTO {$prefix}rollups (name, table_name, id_sequence_name)
VALUES ('{$prefix}requests_rollup_5min', '{$prefix}requests', '{$prefix}requests_id_seq');

INSERT INTO {$prefix}rollups (name, table_name, id_sequence_name)
VALUES ('{$prefix}requests_rollup_1hr', '{$prefix}requests', '{$prefix}requests_id_seq');

INSERT INTO {$prefix}rollups (name, table_name, id_sequence_name)
VALUES ('{$prefix}requests_rollup_1day', '{$prefix}requests', '{$prefix}requests_id_seq');

CREATE TABLE {$prefix}requests (
    id BIGSERIAL,
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    session_uid TEXT NULL,
    url TEXT NULL,
    path TEXT NULL,
    host TEXT NULL,
    search TEXT NULL,
    query_params TEXT NULL,
    utm_source TEXT NULL,
    utm_medium TEXT NULL,
    utm_campaign TEXT NULL,
    utm_content TEXT NULL,
    timestamp TIMESTAMPTZ DEFAULT NOW() NOT NULL
) PARTITION BY RANGE (timestamp);
CREATE INDEX ON public.{$prefix}requests (timestamp);

CREATE TABLE {$prefix}requests_rollup_1min (
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    url TEXT NULL,
    path TEXT NULL,
    host TEXT NULL,
    minute TIMESTAMPTZ NOT NULL,
    request_count bigint
) PARTITION BY RANGE (minute);
CREATE INDEX ON public.{$prefix}requests_rollup_1min (minute);
CREATE UNIQUE INDEX {$prefix}requests_rollup_1min_unique_idx ON {$prefix}requests_rollup_1min(object_uid, model, url, path, host, minute);

CREATE TABLE {$prefix}requests_rollup_5min (
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    url TEXT NULL,
    path TEXT NULL,
    host TEXT NULL,
    minute TIMESTAMPTZ NOT NULL,
    request_count bigint
) PARTITION BY RANGE (minute);
CREATE INDEX ON public.{$prefix}requests_rollup_5min (minute);
CREATE UNIQUE INDEX {$prefix}requests_rollup_5min_unique_idx ON {$prefix}requests_rollup_5min(object_uid, model, url, path, host, minute);

CREATE TABLE {$prefix}requests_rollup_1hr (
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    url TEXT NULL,
    path TEXT NULL,
    host TEXT NULL,
    hour TIMESTAMPTZ NOT NULL,
    request_count bigint
) PARTITION BY RANGE (hour);
CREATE INDEX ON public.{$prefix}requests_rollup_1hr (hour);
CREATE UNIQUE INDEX {$prefix}requests_rollup_1hr_unique_idx ON {$prefix}requests_rollup_1hr(object_uid, model, url, path, host, hour);

CREATE TABLE {$prefix}requests_rollup_1day (
    object_uid TEXT NOT NULL,
    model TEXT NOT NULL,
    url TEXT NULL,
    path TEXT NULL,
    host TEXT NULL,
    day TIMESTAMPTZ NOT NULL,
    request_count bigint
) PARTITION BY RANGE (day);
CREATE INDEX ON public.{$prefix}requests_rollup_1day (day);
CREATE UNIQUE INDEX {$prefix}requests_rollup_1day_unique_idx ON {$prefix}requests_rollup_1day(object_uid, model, url, path, host, day);

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
    INSERT INTO {$prefix}requests_rollup_1min
        SELECT  object_uid,
                model,
                url,
                path,
                host,
                date_trunc('seconds', (timestamp - TIMESTAMP 'epoch') / 60) * 60 + TIMESTAMP 'epoch' AS minute,
                count(*) as request_count
        FROM {$prefix}requests WHERE {$prefix}requests.id BETWEEN start_id AND end_id
        GROUP BY object_uid, model, url, path, host, minute
        ON CONFLICT (object_uid, model, url, path, host, minute)
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
    INSERT INTO {$prefix}requests_rollup_5min
        SELECT  object_uid,
                model,
                url,
                path,
                host,
                date_trunc('seconds', (timestamp - TIMESTAMP 'epoch') / 300) * 300 + TIMESTAMP 'epoch' AS minute,
                count(*) as request_count
        FROM {$prefix}requests WHERE {$prefix}requests.id BETWEEN start_id AND end_id
        GROUP BY object_uid, model, url, path, host, minute
        ON CONFLICT (object_uid, model, url, path, host, minute)
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
    INSERT INTO {$prefix}requests_rollup_1hr
        SELECT  object_uid,
                model,
                url,
                path,
                host,
                date_trunc('hour', timestamp) as hour,
                count(*) as request_count
        FROM {$prefix}requests WHERE {$prefix}requests.id BETWEEN start_id AND end_id
        GROUP BY object_uid, model, url, path, host, hour
        ON CONFLICT (object_uid, model, url, path, host, hour)
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
    INSERT INTO {$prefix}requests_rollup_1day
        SELECT  object_uid,
                model,
                url,
                path,
                host,
                date_trunc('day', timestamp) as day,
                count(*) as request_count
        FROM {$prefix}requests WHERE {$prefix}requests.id BETWEEN start_id AND end_id
        GROUP BY object_uid, model, url, path, host, day
        ON CONFLICT (object_uid, model, url, path, host, day)
        DO UPDATE
        SET request_count = {$prefix}requests_rollup_1day.request_count + excluded.request_count;
END; 
$$
LANGUAGE plpgsql;


        ");
        // object attributes
        Schema::create(("{$prefix}object_attributes"), function (Blueprint $table) {
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->jsonb('attributes')->nullable(false);
            $table->timestamps();
        });

        // Model::join('object_attributes', 'object_uid', 'object_uid')
        //     ->where('model', 'user')
        //     ->where('attributes->age', '>', 18)
        //     ->get();

        Schema::create("{$prefix}object_attributes_audit", function (Blueprint $table) {
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->text('attribute_name')->nullable(false);
            $table->text('attribute_value')->nullable(true);
            $table->date(('attribute_updated_at'))->nullable(false);
            $table->timestamps();

            $table->unique(['model', 'object_uid', 'attribute_name', 'attribute_updated_at']);
            // ON DUPLICATE KEY UPDATE
        });

        Schema::create("{$prefix}object_segment", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->text("{$prefix}segment_id")->nullable(false);
            $table->timestamps();
        });

        Schema::create("{$prefix}object_segment_audit", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->text("{$prefix}segment_id")->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->text('operation')->nullable(false); // enum
            $table->timestamp('recorded_at')->nullable(false);
        });

        Schema::create("{$prefix}segment_groups", function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->timestamps();
        });

        Schema::create("{$prefix}segments", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->unsignedBigInteger("{$prefix}segment_group_id")->nullable(true);
            $table->text('model')->nullable(false);
            $table->jsonb('definition')->nullable(true);
            $table->integer('sort_order')->nullable(false)->default(0);
            $table->timestamps();

            $table->foreign("{$prefix}segment_group_id")->references('id')->on("{$prefix}segment_groups");
            $table->index("{$prefix}segment_group_id");
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = $this->prefix;

        Schema::dropIfExists("{$prefix}segments");
        Schema::dropIfExists("{$prefix}segment_groups");
        Schema::dropIfExists("{$prefix}object_segment_audit");
        Schema::dropIfExists("{$prefix}object_segment");
        Schema::dropIfExists("{$prefix}object_attributes_audit");
        Schema::dropIfExists("{$prefix}requests");
        Schema::dropIfExists("{$prefix}object_attributes");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}requests_rollup_1min CASCADE");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}requests_rollup_5min CASCADE");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}requests_rollup_1hr CASCADE");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}requests_rollup_1day CASCADE");
        Schema::dropIfExists("{$prefix}requests");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}events_rollup_1min CASCADE");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}events_rollup_5min CASCADE");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}events_rollup_1hr CASCADE");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}events_rollup_1day CASCADE");
        Schema::dropIfExists("{$prefix}events");
        Schema::dropIfExists("{$prefix}rollups");
    }
};
