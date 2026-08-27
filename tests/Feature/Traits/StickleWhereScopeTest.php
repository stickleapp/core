<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;
use Workbench\App\Models\Workspace;

/**
 * The model_class column stores a class basename ("User"), never a fully
 * qualified name: every writer goes through the trackable_attributes mutator
 * or ClassUtils::storeModelClass(). A scope that binds anything else joins to
 * nothing and silently returns an empty segment.
 */
test('stickleWhere() joins model attributes written under the stored model class', function (): void {
    $matching = User::factory()->create(['name' => 'Has Documents']);
    $other = User::factory()->create(['name' => 'No Documents']);

    $matching->trackable_attributes = ['document_count' => 5];
    $other->trackable_attributes = ['document_count' => 0];

    $users = User::query()
        ->stickleWhere(Filter::number('document_count')->greaterThan(0))
        ->get();

    expect($users)->toHaveCount(1)
        ->and($users->first()->name)->toBe('Has Documents');
});

/**
 * stickleOrWhere() has to build the join itself here. Called after
 * stickleWhere() it would find the join already present and skip the branch
 * that carries the bug.
 */
test('stickleOrWhere() builds its own join and ors against the existing conditions', function (): void {
    $documents = User::factory()->create(['name' => 'Has Documents']);
    $comments = User::factory()->create(['name' => 'Has Comments']);
    $neither = User::factory()->create(['name' => 'Has Neither']);

    $documents->trackable_attributes = ['comment_count' => 0];
    $comments->trackable_attributes = ['comment_count' => 3];
    $neither->trackable_attributes = ['comment_count' => 0];

    $users = User::query()
        ->where('name', 'Has Documents')
        ->stickleOrWhere(Filter::number('comment_count')->greaterThan(0))
        ->get();

    expect($users->pluck('name')->sort()->values()->all())
        ->toBe(['Has Comments', 'Has Documents']);
});

test('stickleWhere() joins on the model key rather than a hard-coded id column', function (): void {
    $workspace = Workspace::query()->create(['name' => 'Tracked']);
    $other = Workspace::query()->create(['name' => 'Untracked']);

    $workspace->trackable_attributes = ['seat_count' => 12];
    $other->trackable_attributes = ['seat_count' => 0];

    $workspaces = Workspace::query()
        ->stickleWhere(Filter::number('seat_count')->greaterThan(0))
        ->get();

    expect($workspaces)->toHaveCount(1)
        ->and($workspaces->first()->name)->toBe('Tracked');
});

test('stickleOrWhere() joins on the model key rather than a hard-coded id column', function (): void {
    $workspace = Workspace::query()->create(['name' => 'Tracked']);
    $other = Workspace::query()->create(['name' => 'Untracked']);

    $workspace->trackable_attributes = ['seat_count' => 12];
    $other->trackable_attributes = ['seat_count' => 0];

    $workspaces = Workspace::query()
        ->where('name', 'Nothing At All')
        ->stickleOrWhere(Filter::number('seat_count')->greaterThan(0))
        ->get();

    expect($workspaces)->toHaveCount(1)
        ->and($workspaces->first()->name)->toBe('Tracked');
});
