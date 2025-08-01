<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql for text (case insensitive)', function () {

    $filter = Filter::text('a_column')
        ->beginsWith('hello');

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        "select * from \"users\" where data->>'a_column'::text ilike ?"
    );

    expect($builder->getBindings())->toEqual(['hello%']);
});

test('Creates correct sql for text (case sensitive)', function () {

    $filter = Filter::text('a_column')
        ->beginsWith('Hello', true);

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        "select * from \"users\" where data->>'a_column'::text like ?"
    );

    expect($builder->getBindings())->toEqual(['Hello%']);
});

test('Handles special characters in search term', function () {

    $filter = Filter::text('a_column')
        ->beginsWith('hello_world%test');

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        "select * from \"users\" where data->>'a_column'::text ilike ?"
    );

    expect($builder->getBindings())->toEqual(['hello_world%test%']);
});

test('Works with empty string', function () {

    $filter = Filter::text('a_column')
        ->beginsWith('');

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        "select * from \"users\" where data->>'a_column'::text ilike ?"
    );

    expect($builder->getBindings())->toEqual(['%']);
});

test('Works with single character', function () {

    $filter = Filter::text('a_column')
        ->beginsWith('a');

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        "select * from \"users\" where data->>'a_column'::text ilike ?"
    );

    expect($builder->getBindings())->toContain('a%');
});

test('Case sensitivity parameter defaults to false', function () {

    $filter = Filter::text('a_column')
        ->beginsWith('Test');

    expect($filter->test->caseSensitive)->toBeFalse();
});

test('Case sensitivity parameter can be set to true', function () {

    $filter = Filter::text('a_column')
        ->beginsWith('Test', true);

    expect($filter->test->caseSensitive)->toBeTrue();
});

test('Stores comparator correctly', function () {

    $searchTerm = 'hello world';

    $filter = Filter::text('a_column')
        ->beginsWith($searchTerm);

    expect($filter->test->comparator)->toBe($searchTerm);
});

test('Does not throw error when query is executed', function () {

    $query = User::query()
        ->stickleWhere(
            Filter::text('name')
                ->beginsWith('John')
        );

    expect(function () use ($query) {
        $query->get();
    })->not()->toThrow(\Exception::class);
});
