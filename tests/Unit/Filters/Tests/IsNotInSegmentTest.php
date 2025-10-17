<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\Segment as SegmentModel;
use Workbench\App\Models\User;

beforeEach(function (): void {
    // Create a test segment for use in tests
    $this->segment = SegmentModel::query()->create([
        'name' => 'Inactive Users',
        'model_class' => 'User',
        'as_class' => 'InactiveUsers',
        'description' => 'Currently inactive users',
    ]);
});

test('isNotInSegment() generates correct SQL for segment non-membership', function (): void {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segment('InactiveUsers')->isNotInSegment();
    $builder = User::query();

    $target = $filter->getTarget($builder);
    $target->applyJoin();
    $filter->test->applyFilter($builder, $target, 'and');

    $sql = $builder->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment');
    expect($sql)->toContain('is null');
});

test('isNotInSegment() creates executable query', function (): void {
    $filter = Filter::segment('InactiveUsers')->isNotInSegment();
    $builder = User::query();

    $target = $filter->getTarget($builder);
    $target->applyJoin();
    $filter->test->applyFilter($builder, $target, 'and');

    // Verify the query can be executed without errors
    expect(function () use ($builder): void {
        $builder->get();
    })->not()->toThrow(Exception::class);
});

test('isNotInSegment() with stickleWhere integration', function (): void {
    $prefix = config('stickle.database.tablePrefix');

    $query = User::query()
        ->stickleWhere(
            Filter::segment('InactiveUsers')->isNotInSegment()
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment');
    expect($sql)->toContain('is null');
});
