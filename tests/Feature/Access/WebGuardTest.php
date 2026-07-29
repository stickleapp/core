<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;

it('denies the UI when no gate is defined', function (): void {

    withoutStickleGate();

    $this->get('/stickle/live')->assertForbidden();
});

it('denies the UI when the gate denies', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => false);

    $this->get('/stickle/live')->assertForbidden();
});

it('allows the UI when the gate allows', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => true);

    $this->get('/stickle/live')->assertOk();
});
