<?php

declare(strict_types=1);

it('returns activities data via API request', function (): void {

    $queryParams = http_build_query([
        'per_page' => 25,
        'model_class' => 'User',
    ]);

    $response = $this->getJson("/stickle/api/requests?{$queryParams}");

    $response->assertOk();

    $data = $response->json();

    expect($data)->toHaveKeys(['data']);
    expect($data['data'])->toBeArray();
});
