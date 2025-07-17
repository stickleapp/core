<?php

use Carbon\Carbon;
use StickleApp\Core\Models\ModelAttributeAudit;
use StickleApp\Core\Models\ModelAttributes;
use StickleApp\Core\Support\StickleAttributeAccessor;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->accessor = new StickleAttributeAccessor($this->user, 'shoe_size');

    // Set up test data
    ModelAttributes::create([
        'model_class' => class_basename($this->user),
        'object_uid' => $this->user->getKey(),
        'data' => ['shoe_size' => 42],
        'synced_at' => now(),
    ]);

    // Create attribute history
    ModelAttributeAudit::create([
        'model_class' => class_basename($this->user),
        'object_uid' => $this->user->getKey(),
        'attribute' => 'shoe_size',
        'value_old' => 41,
        'value_new' => 42,
        'change_type' => 'update',
        'created_at' => Carbon::parse('2023-05-15'),
    ]);

    ModelAttributeAudit::create([
        'model_class' => class_basename($this->user),
        'object_uid' => $this->user->getKey(),
        'attribute' => 'shoe_size',
        'value_old' => 40,
        'value_new' => 41,
        'change_type' => 'update',
        'created_at' => Carbon::parse('2023-03-10'),
    ]);

    ModelAttributeAudit::create([
        'model_class' => class_basename($this->user),
        'object_uid' => $this->user->getKey(),
        'attribute' => 'shoe_size',
        'value_old' => null,
        'value_new' => 40,
        'change_type' => 'create',
        'created_at' => Carbon::parse('2023-01-05'),
    ]);
});

it('gets current value', function () {
    $value = $this->accessor->current();

    expect($value)->toBe(42);
});

it('gets all historical values', function () {
    $values = $this->accessor->audit()->all();

    expect($values)->toHaveCount(3);
    expect($values[0])->toBe(42);
    expect($values[1])->toBe(41);
    expect($values[2])->toBe(40);
});

it('gets values between dates', function () {
    $values = $this->accessor->audit()
        ->between('2023-02-01', '2023-06-01')
        ->all();

    expect($values)->toHaveCount(2);
    expect($values[0])->toBe(42);
    expect($values[1])->toBe(41);
});

it('limits historical values', function () {
    $values = $this->accessor->audit()
        ->limit(2)
        ->all();

    expect($values)->toHaveCount(2);
    expect($values[0])->toBe(42);
    expect($values[1])->toBe(41);
});

it('gets latest value', function () {
    $value = $this->accessor->latest();

    expect($value)->toBe(42);
});

it('gets timeline with metadata', function () {
    $timeline = $this->accessor->audit()->timeline();

    expect($timeline)->toHaveCount(3);

    // Check first entry
    expect($timeline[0]['value'])->toBe(42);
    expect($timeline[0]['old_value'])->toBe(41);
    expect($timeline[0]['change'])->toBe('update');
    expect($timeline[0]['timestamp']->format('Y-m-d'))->toBe('2023-05-15');

    // Check last entry
    expect($timeline[2]['value'])->toBe(40);
    expect($timeline[2]['old_value'])->toBeNull();
    expect($timeline[2]['change'])->toBe('create');
});

it('converts to string', function () {
    $string = (string) $this->accessor;

    expect($string)->toBe('42');
});

it('converts non-scalar values to json string', function () {
    // Set up a JSON value
    ModelAttributes::where([
        'model_class' => class_basename($this->user),
        'object_uid' => $this->user->getKey(),
    ])->update([
        'data' => ['shoe_size' => ['us' => 10, 'eu' => 42]],
    ]);

    $string = (string) $this->accessor;

    expect($string)->toBe('{"us":10,"eu":42}');
});

it('gets current timeline when not in audit mode', function () {
    $timeline = $this->accessor->timeline();

    expect($timeline)->toHaveCount(1);
    expect($timeline[0]['value'])->toBe(42);
    expect($timeline[0])->toHaveKey('timestamp');
});

it('handles missing attributes gracefully', function () {
    $accessor = new StickleAttributeAccessor($this->user, 'nonexistent_attribute');

    expect($accessor->current())->toBeNull();
    expect($accessor->all())->toHaveCount(1);
    expect($accessor->value())->toBeNull();
});
