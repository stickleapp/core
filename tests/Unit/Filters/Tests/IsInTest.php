<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\Segment as SegmentModel;
use Workbench\App\Models\User;

beforeEach(function () {
    // Create a test segment for use in tests
    $this->segment = SegmentModel::create([
        'name' => 'Active Users',
        'model_class' => 'User',
        'as_class' => 'ActiveUsers',
        'description' => 'Currently active users',
    ]);
});

test('isIn() generates correct SQL for segment membership', function () {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segment('ActiveUsers')->isIn();
    $builder = User::query();

    $target = $filter->getTarget($builder);
    $target->applyJoin();
    $filter->test->applyFilter($builder, $target, 'and');

    $sql = $builder->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment');
    expect($sql)->toContain('is not null');
});

test('isIn() creates executable query', function () {
    $filter = Filter::segment('ActiveUsers')->isIn();
    $builder = User::query();

    $target = $filter->getTarget($builder);
    $target->applyJoin();
    $filter->test->applyFilter($builder, $target, 'and');

    // Verify the query can be executed without errors
    expect(function () use ($builder) {
        $builder->get();
    })->not()->toThrow(\Exception::class);
});

test('isIn() with stickleWhere integration', function () {
    $prefix = config('stickle.database.tablePrefix');

    $query = User::query()
        ->stickleWhere(
            Filter::segment('ActiveUsers')->isIn()
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment');
    expect($sql)->toContain('is not null');
});
