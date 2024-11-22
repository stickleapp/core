<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Contracts;

/**
 * @internal
 */
interface AnalyticsRepository
{
    public function saveEvent(
        string $model,
        string $objectUid,
        string $sessionUid,
        string $event,
        ?array $properties = [],
        ?array $pageProperties = []
    ): void;

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
    ): void;
}
