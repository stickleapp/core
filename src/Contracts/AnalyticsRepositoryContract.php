<?php

declare(strict_types=1);

namespace StickleApp\Core\Contracts;

use DateTimeInterface;

/**
 * @internal
 */
interface AnalyticsRepositoryContract
{
    public function saveRequest(
        string $type,
        string $modelClass,
        string $objectUid,
        string $sessionUid,
        string $ipAddress,
        DateTimeInterface $timestamp,
        ?array $properties = [],
    ): void;

    public function rollupSessions(
        DateTimeInterface $startDate
    ): void;
}
