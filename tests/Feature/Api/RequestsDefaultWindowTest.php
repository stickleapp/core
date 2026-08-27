<?php

declare(strict_types=1);

use StickleApp\Core\Models\Request as StickleRequest;
use Workbench\App\Models\User;

/**
 * The live pane defaulted to the last 30 minutes, so any application quiet for
 * half an hour rendered "No recent events" -- which reads as tracking being
 * broken rather than as nothing having happened.
 */
test('the requests endpoint defaults to a window wide enough to show a quiet day', function (): void {
    $user = User::factory()->create();

    StickleRequest::query()->create([
        'type' => 'request',
        'model_class' => 'User',
        'object_uid' => (string) $user->id,
        'session_uid' => null,
        'ip_address' => '127.0.0.1',
        'timestamp' => now()->subHours(6),
        'properties' => ['name' => 'dashboard'],
    ]);

    $response = $this->getJson('/stickle/api/requests?'.http_build_query([
        'model_class' => 'User',
    ]));

    $response->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});
