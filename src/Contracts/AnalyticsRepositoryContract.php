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
     * Aggregate newly written requests into the rollup table for one grain:
     * '1min', '5min', '1hr' or '1day'.
     *
     * Deliberately typed as a plain string rather than narrowed to those four
     * literals. The grain is interpolated into a SQL function name, so the
     * implementation whitelists it at runtime -- and narrowing here would make
     * that guard provably dead code, which PHPStan reports as an error rather
     * than letting an interface's docblock stand in for a check.
     */
    public function rollupRequests(
        string $grain
    ): void;
}
