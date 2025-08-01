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
     * Save an event
     */
    public function saveEvent(
        string $model,
        string $objectUid,
        string $sessionUid,
        DateTimeInterface $timestamp,
        string $event,
        ?array $properties = [],
    ): void {
        DB::table($this->prefix.'events')->insert([
            'model_class' => $model,
            'object_uid' => $objectUid,
            'session_uid' => $sessionUid,
            'event_name' => $event,
            'properties' => json_encode($properties),
            'timestamp' => $timestamp,
        ]);
    }

    /**
     * Save a request
     */
    public function saveRequest(
        string $model,
        string $objectUid,
        string $sessionUid,
        DateTimeInterface $timestamp,
        ?string $url = null,
        ?string $path = null,
        ?string $host = null,
        ?string $search = null,
        ?string $queryParams = null,
        ?string $utmSource = null,
        ?string $utmMedium = null,
        ?string $utmCampaign = null,
        ?string $utmContent = null
    ): void {
        DB::table($this->prefix.'requests')->insert([
            'model_class' => $model,
            'object_uid' => $objectUid,
            'session_uid' => $sessionUid,
            'url' => $url,
            'path' => $path,
            'host' => $host,
            'search' => $search,
            'query_params' => $queryParams,
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'utm_content' => $utmContent,
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
            (SELECT model_class, object_uid, session_uid, timestamp FROM {$this->prefix}events WHERE timestamp >= '%s'
             UNION ALL
             SELECT model_class, object_uid, session_uid, timestamp FROM {$this->prefix}requests WHERE offline = FALSE AND timestamp > '%s') AS combined
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
}
