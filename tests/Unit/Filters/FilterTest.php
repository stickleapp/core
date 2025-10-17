<?php

declare(strict_types=1);

namespace FilterTest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Traits\StickleEntity;

class User extends Model
{
    use HasFactory;
    use StickleEntity;

    protected $table = 'users';
}

test('example', function (): void {

    $query = User::query()
        ->stickleWhere(
            Filter::eventCount('clicked:something')
                ->count()
                ->greaterThan(10)
                ->betweenDates(startDate: now()->subYears(1), endDate: now())
        );
    expect($query->toSql())->not()->toBeEmpty();
});

test('text filter with equals generates correct full SQL', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $query = User::query()
        ->stickleWhere(
            Filter::text('name')
                ->equals('John Doe')
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("data->>'name'::text = ?");

    $bindings = $query->getBindings();
    expect($bindings)->toContain('John Doe');
});

test('number filter with greater than generates correct full SQL', function (): void {

    $query = User::query()
        ->stickleWhere(
            Filter::number('age')
                ->greaterThan(25)
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("(data->'age')::numeric > ?");

    $bindings = $query->getBindings();
    expect($bindings)->toContain(25);
});

test('boolean filter with isTrue generates correct full SQL', function (): void {

    $query = User::query()
        ->stickleWhere(
            Filter::boolean('is_active')
                ->isTrue()
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("(data->'is_active')::boolean = true");
});

test('date filter with between generates correct full SQL', function (): void {

    $startDate = now()->subMonths(6)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    $query = User::query()
        ->stickleWhere(
            Filter::date('created_at')
                ->between($startDate, $endDate)
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("data->>'created_at'::date between ? and ?");

    expect($query->getBindings())->toContain($startDate, $endDate);
});

test('multiple filters with AND operator generates correct full SQL', function (): void {

    $query = User::query()
        ->stickleWhere(
            Filter::text('status')
                ->equals('active')
        )
        ->stickleWhere(
            Filter::number('score')
                ->greaterThanOrEqualTo(100)
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('where');
    expect($sql)->toContain("data->>'status'::text = ?");
    expect($sql)->toContain("and (data->'score')::numeric >= ?");

    expect($query->getBindings())->toContain('active', 100);
});

test('text filter with contains generates correct full SQL', function (): void {

    $query = User::query()
        ->stickleWhere(
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

test('eventCount filter generates correct full SQL with joins', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $query = User::query()
        ->stickleWhere(
            Filter::eventCount('page_view')
                ->count()
                ->greaterThan(5)
                ->betweenDates(startDate: now()->subDays(30), endDate: now())
        );

    $sql = $query->toSql();

    expect($sql)->toContain('select * from "users"');
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'requests_rollup_1day');
    expect($sql)->toContain('where');
    expect($sql)->toContain('name');
    expect($sql)->toContain('> ?');

    $bindings = $query->getBindings();
    expect($bindings)->not()->toBeEmpty();
});

test('Base class aggregate methods set target arguments correctly', function (): void {
    $filter = Filter::requestCount(url: '/api/users');

    // Test each aggregate method
    $sumFilter = $filter->sum();
    expect($sumFilter->targetArguments['aggregate'])->toBe('sum');

    $avgFilter = $filter->avg();
    expect($avgFilter->targetArguments['aggregate'])->toBe('avg');

    $minFilter = $filter->min();
    expect($minFilter->targetArguments['aggregate'])->toBe('min');

    $maxFilter = $filter->max();
    expect($maxFilter->targetArguments['aggregate'])->toBe('max');

    $countFilter = $filter->count();
    expect($countFilter->targetArguments['aggregate'])->toBe('count');
});

test('Base class delta methods set target arguments correctly', function (): void {
    $filter = Filter::requestCount(url: '/api/users');

    $increasedFilter = $filter->increased();
    expect($increasedFilter->targetArguments['deltaVerb'])->toBe('increased');

    $decreasedFilter = $filter->decreased();
    expect($decreasedFilter->targetArguments['deltaVerb'])->toBe('decreased');

    $changedFilter = $filter->changed();
    expect($changedFilter->targetArguments['deltaVerb'])->toBe('changed');
});

test('Base class date range methods set target arguments correctly', function (): void {
    $startDate = now()->subDays(7);
    $endDate = now();
    $compareToRange = [now()->subDays(14), now()->subDays(7)];
    $currentRange = [now()->subDays(7), now()];

    $filter = Filter::requestCount(url: '/api/users');

    $betweenDatesFilter = $filter->betweenDates($startDate, $endDate);
    expect($betweenDatesFilter->targetArguments['currentDateRange'])->toBe([$startDate, $endDate]);

    $betweenDateRangesFilter = $filter->betweenDateRanges($compareToRange, $currentRange);
    expect($betweenDateRangesFilter->targetArguments['compareToDateRange'])->toBe($compareToRange);
    expect($betweenDateRangesFilter->targetArguments['currentDateRange'])->toBe($currentRange);
});

test('complex filter combination generates executable SQL', function (): void {

    $query = User::query()
        ->stickleWhere(
            Filter::text('email')
                ->contains('@gmail.com')
        )
        ->stickleWhere(
            Filter::boolean('is_verified')
                ->isTrue()
        )
        ->stickleOrWhere(
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
    expect(function () use ($query): void {
        $query->get();
    })->not()->toThrow(Exception::class);
});
