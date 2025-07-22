<?php

declare(strict_types=1);

namespace FilterTest;

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Traits\StickleEntity;

class User extends \Illuminate\Database\Eloquent\Model
{
    use StickleEntity;

    protected $table = 'users';
}

test('example', function () {

    $query = User::query()
        ->stickle(
            Filter::eventCount('clicked:something')
                ->greaterThan(10)
                ->startDate(now()->subYears(1))
                ->endDate(now())
        );

    expect($query->toSql())->not()->toBeEmpty();
});

test('text filter with equals generates correct full SQL', function () {

    $prefix = config('stickle.database.tablePrefix');

    $query = User::query()
        ->stickle(
            Filter::text('name')
                ->equals('John Doe')
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("(data->>'name')::text = ?");

    $bindings = $query->getBindings();
    expect($bindings)->toContain('John Doe');
});

test('number filter with greater than generates correct full SQL', function () {

    $query = User::query()
        ->stickle(
            Filter::number('age')
                ->greaterThan(25)
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("(data->>'age')::numeric > ?");

    $bindings = $query->getBindings();
    expect($bindings)->toContain(25);
});

test('boolean filter with isTrue generates correct full SQL', function () {

    $query = User::query()
        ->stickle(
            Filter::boolean('is_active')
                ->isTrue()
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("(data->>'is_active')::boolean = true");
});

test('date filter with between generates correct full SQL', function () {

    $startDate = now()->subMonths(6)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    $query = User::query()
        ->stickle(
            Filter::date('created_at')
                ->between($startDate, $endDate)
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("(data->>'created_at')::date between ? and ?");

    expect($query->getBindings())->toContain($startDate, $endDate);
});

test('multiple filters with AND operator generates correct full SQL', function () {

    $query = User::query()
        ->stickle(
            Filter::text('status')
                ->equals('active')
        )
        ->stickle(
            Filter::number('score')
                ->greaterThanOrEqualTo(100)
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("(data->>'status')::text = ?");
    expect($sql)->toContain("and (data->>'score')::numeric >= ?");

    expect($query->getBindings())->toContain('active', 100);
});

test('text filter with contains generates correct full SQL', function () {

    $query = User::query()
        ->stickle(
            Filter::text('description')
                ->contains('test')
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain('ilike');
    expect($sql)->toContain('?');

    expect($query->getBindings())->toContain('%test%');
});

test('eventCount filter generates correct full SQL with joins', function () {

    $prefix = config('stickle.database.tablePrefix');

    $query = User::query()
        ->stickle(
            Filter::eventCount('page_view')
                ->greaterThan(5)
                ->between(now()->subDays(30), now())
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'events_rollup_1day');
    expect($sql)->toContain('where');
    expect($sql)->toContain('event_name');
    expect($sql)->toContain('> ?');

    $bindings = $query->getBindings();
    expect($bindings)->not()->toBeEmpty();
});

test('complex filter combination generates executable SQL', function () {

    $query = User::query()
        ->stickle(
            Filter::text('email')
                ->contains('@gmail.com')
        )
        ->stickle(
            Filter::boolean('is_verified')
                ->isTrue()
        )
        ->orStickle(
            Filter::number('login_count')
                ->greaterThan(10)
        );

    $sql = $query->toSql();

    // Verify structure
    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain('ilike');
    expect($sql)->toContain('= true');
    expect($sql)->toContain('> ?');

    // Verify bindings
    expect($query->getBindings())->toContain('%@gmail.com%', 10);

    // Most importantly - verify the query can be executed without errors
    expect(function () use ($query) {
        $query->get();
    })->not()->toThrow(\Exception::class);
});
