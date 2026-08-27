<?php

declare(strict_types=1);

use StickleApp\Core\Enums\ChartType;
use StickleApp\Core\Enums\DataType;
use StickleApp\Core\Enums\PrimaryAggregate;
use StickleApp\Core\Views\Components\Ui\Charts\Model;
use StickleApp\Core\Views\Components\Ui\Charts\ModelRelationship;
use StickleApp\Core\Views\Components\Ui\Charts\Models;
use StickleApp\Core\Views\Components\Ui\Charts\Segment;
use Workbench\App\Models\User;

/**
 * D4: these components type-hint StickleApp\Core\Enums\AggregateType, which
 * does not exist. It has never thrown because the value is always null and PHP
 * does not resolve a parameter's class type when null is passed to a nullable
 * parameter. Passing a non-null aggregate is the only thing that surfaces it,
 * which is exactly what fixing D1 to D3 starts doing.
 */
test('Charts\Model accepts a non-null primary aggregate', function (): void {
    $component = new Model(
        apiPrefix: 'stickle/api',
        key: 'user_rating',
        model: new User,
        attribute: 'user_rating',
        chartType: ChartType::LINE,
        currentValue: 5,
        label: 'User Rating',
        description: null,
        dataType: DataType::INTEGER,
        primaryAggregate: PrimaryAggregate::AVG,
    );

    expect($component->primaryAggregate)->toBe(PrimaryAggregate::AVG);
});

test('Charts\Models accepts a non-null primary aggregate', function (): void {
    $component = new Models(
        apiPrefix: 'stickle/api',
        key: 'user_rating',
        modelClass: User::class,
        attribute: 'user_rating',
        chartType: ChartType::LINE,
        label: 'User Rating',
        description: null,
        dataType: DataType::INTEGER,
        primaryAggregate: PrimaryAggregate::AVG,
    );

    expect($component->primaryAggregate)->toBe(PrimaryAggregate::AVG);
});

test('Charts\Segment accepts a non-null primary aggregate', function (): void {
    $component = new Segment(
        apiPrefix: 'stickle/api',
        key: 'user_rating',
        segment: new stdClass,
        attribute: 'user_rating',
        chartType: ChartType::LINE,
        label: 'User Rating',
        description: null,
        dataType: DataType::INTEGER,
        primaryAggregate: PrimaryAggregate::AVG,
    );

    expect($component->primaryAggregate)->toBe(PrimaryAggregate::AVG);
});

test('Charts\ModelRelationship accepts a non-null primary aggregate', function (): void {
    $component = new ModelRelationship(
        apiPrefix: 'stickle/api',
        key: 'user_rating',
        model: new User,
        relationship: 'tickets',
        attribute: 'user_rating',
        chartType: ChartType::LINE,
        currentValue: 5,
        label: 'User Rating',
        description: null,
        dataType: DataType::INTEGER,
        primaryAggregate: PrimaryAggregate::AVG,
    );

    expect($component->primaryAggregate)->toBe(PrimaryAggregate::AVG);
});
