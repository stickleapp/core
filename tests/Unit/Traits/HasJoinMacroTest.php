<?php

declare(strict_types=1);

use Workbench\App\Models\User;

/**
 * hasJoin($table, $alias) compares the alias for a subquery join but ignored
 * it entirely for a plain one, matching on the table name alone. A caller that
 * passes table and alias separately -- the signature's own two-argument form --
 * therefore could not tell two aliased joins on one table apart.
 */
test('hasJoin matches a plain join by table and alias', function (): void {
    $builder = User::query();
    $prefix = config('stickle.database.tablePrefix');

    $builder->leftJoin($prefix.'model_segment as alpha', function ($join) use ($prefix): void {
        $join->on('users.id', '=', $prefix.'model_segment.object_uid');
    });

    expect($builder->hasJoin($prefix.'model_segment', 'alpha'))->toBeTrue()
        ->and($builder->hasJoin($prefix.'model_segment', 'beta'))->toBeFalse();
});

test('hasJoin still matches an unaliased join by table alone', function (): void {
    $builder = User::query();
    $prefix = config('stickle.database.tablePrefix');

    $builder->leftJoin($prefix.'model_segment', 'users.id', '=', $prefix.'model_segment.object_uid');

    expect($builder->hasJoin($prefix.'model_segment'))->toBeTrue();
});
