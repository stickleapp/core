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
}
