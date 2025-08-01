<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\Segment as SegmentModel;
use Workbench\App\Models\User;

beforeEach(function () {
    // Create a test segment for use in tests
    $this->segment = SegmentModel::create([
        'name' => 'Inactive Users',
        'model_class' => 'User',
        'as_class' => 'InactiveUsers',
        'description' => 'Currently inactive users',
    ]);
});

test('isNotIn() generates correct SQL for segment non-membership', function () {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segment('InactiveUsers')->isNotIn();
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

test('isNotIn() creates executable query', function () {
    $filter = Filter::segment('InactiveUsers')->isNotIn();
    $builder = User::query();

    $target = $filter->getTarget($builder);
    $target->applyJoin();
    $filter->test->applyFilter($builder, $target, 'and');

    // Verify the query can be executed without errors
    expect(function () use ($builder) {
        $builder->get();
    })->not()->toThrow(\Exception::class);
});

test('isNotIn() with stickleWhere integration', function () {
    $prefix = config('stickle.database.tablePrefix');

    $query = User::query()
        ->stickleWhere(
            Filter::segment('InactiveUsers')->isNotIn()
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment');
    expect($sql)->toContain('is null');
});
