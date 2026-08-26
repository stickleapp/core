CREATE OR REPLACE FUNCTION stc_rollup_requests_1min(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which requests we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM stc_incremental_rollup_window('stc_requests_rollup_1min');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the requests, merge results if the entry already exists */
    /* The column list is required, not decorative: the table declares
       object_uid before model_class, so an unqualified INSERT maps this
       SELECT positionally and silently transposes the two. */
    INSERT INTO stc_requests_rollup_1min
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
        FROM stc_requests WHERE stc_requests.id BETWEEN start_id AND end_id
        GROUP BY model_class, object_uid, type, name, title, path, url, minute
        ON CONFLICT (model_class, object_uid, type, name, title, path, url, minute)
        DO UPDATE
        SET request_count = stc_requests_rollup_1min.request_count + excluded.request_count;
END; 
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION stc_rollup_requests_5min(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which requests we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM stc_incremental_rollup_window('stc_requests_rollup_5min');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the requests, merge results if the entry already exists */
    /* The column list is required, not decorative: the table declares
       object_uid before model_class, so an unqualified INSERT maps this
       SELECT positionally and silently transposes the two. */
    INSERT INTO stc_requests_rollup_5min
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
        FROM stc_requests WHERE stc_requests.id BETWEEN start_id AND end_id
        GROUP BY model_class, object_uid, type, name, title, path, url, minute
        ON CONFLICT (model_class, object_uid, type, name, title, path, url, minute)
        DO UPDATE
        SET request_count = stc_requests_rollup_5min.request_count + excluded.request_count;
END; 
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION stc_rollup_requests_1hr(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which requests we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM stc_incremental_rollup_window('stc_requests_rollup_1hr');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the requests, merge results if the entry already exists */
    /* The column list is required, not decorative: the table declares
       object_uid before model_class, so an unqualified INSERT maps this
       SELECT positionally and silently transposes the two. */
    INSERT INTO stc_requests_rollup_1hr
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
        FROM stc_requests WHERE stc_requests.id BETWEEN start_id AND end_id
        GROUP BY model_class, object_uid, type, name, title, path, url, hour
        ON CONFLICT (model_class, object_uid, type, name, title, path, url, hour)
        DO UPDATE
        SET request_count = stc_requests_rollup_1hr.request_count + excluded.request_count;
END; 
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION stc_rollup_requests_1day(OUT start_id bigint, OUT end_id bigint)
RETURNS record
AS
$$
BEGIN
    /* determine which requests we can safely aggregate */
    SELECT window_start, window_end INTO start_id, end_id
    FROM stc_incremental_rollup_window('stc_requests_rollup_1day');

    /* exit early if there are no new events to aggregate */
    IF start_id > end_id THEN RETURN; END IF;

    /* aggregate the requests, merge results if the entry already exists */
    /* The column list is required, not decorative: the table declares
       object_uid before model_class, so an unqualified INSERT maps this
       SELECT positionally and silently transposes the two. */
    INSERT INTO stc_requests_rollup_1day
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
        FROM stc_requests WHERE stc_requests.id BETWEEN start_id AND end_id
        GROUP BY model_class, object_uid, type, name, title, path, url, day
        ON CONFLICT (model_class, object_uid, type, name, title, path, url, day)
        DO UPDATE
        SET request_count = stc_requests_rollup_1day.request_count + excluded.request_count;
END; 
$$
LANGUAGE plpgsql;
