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

it('lets a tracked model_class through validation', function (): void {

    User::factory()->create();

    $this->withoutExceptionHandling();

    /**
     * Reaching the session lookup is the assertion: validation accepted
     * 'User' rather than rejecting it or failing to resolve a rule named
     * after the class.
     *
     * The request still cannot succeed. IngestController reads
     * $request->session() for session_uid, but the route is registered in the
     * api middleware group, which starts no session -- so a valid payload
     * raises here on a stock install. When that is fixed, replace this with
     * assertNoContent() and an assertion on the recorded row.
     */
    expect(fn () => $this->postJson('/stickle/api/track', [
        'payload' => [[
            'type' => 'page',
            'model_class' => 'User',
            'object_uid' => '1',
        ]],
    ]))->toThrow(RuntimeException::class, 'Session store not set on request');
});
