<?php

declare(strict_types=1);

use StickleApp\Core\Jobs\RecordModelAttributesJob;
use Workbench\App\Models\Customer;
use Workbench\App\Models\Ticket;

/**
 * Customer is the model an installing application copies. It declared its
 * tracked attributes with #[StickleTrackedAttribute], which was added in
 * d590019 and removed again in 5cdbfc5 -- a revert that converted User back to
 * the static methods but left Customer behind. Nothing has read those
 * annotations since, so Customer has tracked nothing at all.
 */
test('the workbench Customer records every attribute it declares as tracked', function (): void {
    $customer = Customer::factory()->create();

    (new RecordModelAttributesJob($customer))->handle();

    expect($customer->modelAttributes()->value('data'))->toHaveKeys([
        'ticket_count',
        'open_ticket_count',
        'tickets_closed',
        'tickets_closed_last_30_days',
        'average_resolution_time',
        'average_resolution_time_30_days',
        'mrr',
    ]);
});

test('a tracked accessor records the value it computes', function (): void {
    $customer = Customer::factory()->create();

    Ticket::factory()->count(3)->create([
        'customer_id' => $customer->id,
        'status' => 'open',
    ]);

    (new RecordModelAttributesJob($customer))->handle();

    $data = $customer->modelAttributes()->value('data');

    expect($data['ticket_count'])->toBe(3)
        ->and($data['open_ticket_count'])->toBe(3)
        ->and($data['tickets_closed'])->toBe(0);
});

test('the workbench Customer observes mrr when it is created', function (): void {
    $customer = Customer::factory()->create();

    expect($customer->modelAttributes()->value('data'))->toHaveKey('mrr');
});
