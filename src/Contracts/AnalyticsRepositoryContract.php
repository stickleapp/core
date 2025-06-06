<?php

declare(strict_types=1);

namespace StickleApp\Core\Contracts;

use DateTimeInterface;

/**
 * @internal
 */
interface AnalyticsRepositoryContract
{
    /**
     * @param  array<int, string>  $properties
     */
    public function saveEvent(
        string $model,
        string $objectUid,
        string $sessionUid,
        DateTimeInterface $timestamp,
        string $event,
        ?array $properties = [],
    ): void;

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
    ): void;

    public function rollupSessions(
        DateTimeInterface $startDate
    ): void;
}
