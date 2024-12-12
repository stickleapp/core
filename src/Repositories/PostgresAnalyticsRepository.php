<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Repositories;

use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
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
        #[Config('cascade.database.tablePrefix')] protected ?string $prefix = null,
    ) {}

    /**
     * Save an event
     */
    public function saveEvent(
        string $model,
        string $objectUid,
        string $sessionUid,
        string $event,
        ?array $properties = [],
        ?array $pageProperties = []
    ): void {
        DB::table($this->table('events'))->insert([
            'object_uid' => $objectUid,
            'model' => $model,
            'session_uid' => $sessionUid,
            'event_name' => $event,
            'properties' => json_encode($properties),
            'page_properties' => json_encode($pageProperties),
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }

    /**
     * Save a request
     */
    public function saveRequest(
        string $model,
        string $objectUid,
        string $sessionUid,
        ?string $url,
        ?string $path,
        ?string $host,
        ?string $search,
        ?string $queryParams,
        ?string $utmSource,
        ?string $utmMedium,
        ?string $utmCampaign,
        ?string $utmContent
    ): void {
        DB::table($this->table('requests'))->insert([
            'object_uid' => $objectUid,
            'model' => $model,
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
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }

    private function table(string $name): string
    {
        return $this->prefix.$name;
    }
}
