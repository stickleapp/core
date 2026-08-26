<?php

declare(strict_types=1);

use StickleApp\Core\Models\Request as RequestModel;
use Workbench\App\Models\User;

/**
 * model_class is validated against the models using the StickleEntity trait.
 * The rule has to be a real rule object: an array of class names sitting in the
 * rules array is read by Laravel as a list of rule *names*, so it looks for a
 * validator method named after the class and raises BadMethodCallException --
 * on a public, unauthenticated endpoint.
 *
 * The rule also compares basenames. getClassesWithTrait() returns fully
 * qualified names while model_class is sent and stored as a basename, so a
 * rule built straight from it would reject every legitimate value.
 */
it('rejects an untracked model_class through validation, not a missing rule', function (): void {

    $this->withoutExceptionHandling();

    expect(fn () => $this->postJson('/stickle/api/track', [
        'payload' => [[
            'type' => 'page',
            'model_class' => 'NotATrackedModel',
            'object_uid' => '1',
        ]],
    ]))->toThrow(Exception::class, 'Request invalid');

    expect(RequestModel::query()->where('model_class', 'NotATrackedModel')->exists())->toBeFalse();
});

it('accepts a tracked model_class and records it', function (): void {

    $user = User::factory()->create();

    /** PageListener is queued; the test queue is database, so run it inline. */
    config()->set('queue.default', 'sync');

    $this->postJson('/stickle/api/track', [
        'payload' => [[
            'type' => 'page',
            'model_class' => 'User',
            'object_uid' => (string) $user->getKey(),
            'properties' => ['name' => 'Home', 'url' => 'https://example.test/pricing?plan=pro'],
        ]],
    ])->assertNoContent();

    $request = RequestModel::query()
        ->where('model_class', 'User')
        ->where('object_uid', (string) $user->getKey())
        ->sole();

    /**
     * The api middleware group starts no session, so session_uid is null
     * rather than the request raising "Session store not set on request".
     */
    expect($request->session_uid)->toBeNull()
        ->and($request->type)->toBe('request');

    /**
     * path and search describe the reported page, not the beacon POST that
     * carried it -- which used to record every row at /stickle/api/track.
     */
    expect($request->properties['path'])->toBe('/pricing')
        ->and($request->properties['search'])->toBe('plan=pro');
});
