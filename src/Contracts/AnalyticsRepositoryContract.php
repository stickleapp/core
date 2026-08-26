<?php

declare(strict_types=1);

namespace StickleApp\Core\Contracts;

use DateTimeInterface;

/**
 * @internal
 */
interface AnalyticsRepositoryContract
{
    public function rollupSessions(
        DateTimeInterface $startDate
    ): void;

    /**
     * Aggregate newly written requests into the rollup table for one grain.
     *
     * @param  '1min'|'5min'|'1hr'|'1day'  $grain
     */
    public function rollupRequests(
        string $grain
    ): void;
}
