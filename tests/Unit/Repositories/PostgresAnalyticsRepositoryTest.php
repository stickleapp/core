<?php

declare(strict_types=1);

use StickleApp\Core\Repositories\PostgresAnalyticsRepository;

it('can be instantiated', function () {
    $repository = new PostgresAnalyticsRepository;
    expect($repository)->toBeInstanceOf(PostgresAnalyticsRepository::class);
});
