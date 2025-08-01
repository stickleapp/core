<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\Segment as SegmentModel;
use Workbench\App\Models\User;

beforeEach(function () {
    // Create a test segment for use in tests
    $this->segment = SegmentModel::create([
        'name' => 'Premium Users',
        'model_class' => 'User',
        'as_class' => 'PremiumUsers',
        'description' => 'Users who have never been premium',
    ]);
});

test('hasNeverBeenIn() generates correct SQL for never having segment membership', function () {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segmentHistory('PremiumUsers')->hasNeverBeenIn();
    $builder = User::query();

    $target = $filter->getTarget($builder);
    $target->applyJoin();
    $filter->test->applyFilter($builder, $target, 'and');

    $sql = $builder->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment_audit');
    expect($sql)->toContain('is null');
    expect($sql)->toContain('operation');
});

test('hasNeverBeenIn() creates executable query', function () {
    $filter = Filter::segmentHistory('PremiumUsers')->hasNeverBeenIn();
    $builder = User::query();

    $target = $filter->getTarget($builder);
    $target->applyJoin();
    $filter->test->applyFilter($builder, $target, 'and');

    // Verify the query can be executed without errors
    expect(function () use ($builder) {
        $builder->get();
    })->not()->toThrow(\Exception::class);
});

test('hasNeverBeenIn() with stickleWhere integration', function () {
    $prefix = config('stickle.database.tablePrefix');

    $query = User::query()
        ->stickleWhere(
            Filter::segmentHistory('PremiumUsers')->hasNeverBeenIn()
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment_audit');
    expect($sql)->toContain('is null');
});
