<?php

declare(strict_types=1);

namespace StickleApp\Core\Repositories;

use DateTimeInterface;
use StickleApp\\Core\Core\Contracts\AnalyticsRepository;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class PostgresAnalyticsRepository implements AnalyticsRepository
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[Config('STICKLE.database.tablePrefix')] protected ?string $prefix = null,
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
        ?array $pageProperties = []
    ): void {
        DB::table($this->prefix.'events')->insert([
            'model' => $model,
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
            'model' => $model,
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
}
