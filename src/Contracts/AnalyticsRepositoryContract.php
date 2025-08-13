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
        ?array $properties = [],
    ): void;

    public function saveRequest(
        string $model,
        string $objectUid,
        string $sessionUid,
        DateTimeInterface $timestamp,
        ?array $properties = [],
    ): void;

    public function rollupSessions(
        DateTimeInterface $startDate
    ): void;
}
