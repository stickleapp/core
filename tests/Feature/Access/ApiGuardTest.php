<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;

it('denies a read endpoint when no gate is defined', function (): void {

    withoutStickleGate();

    $this->getJson('/stickle/api/segments')->assertForbidden();
});

it('denies a read endpoint when the gate denies', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => false);

    $this->getJson('/stickle/api/segments')->assertForbidden();
});

it('allows a read endpoint when the gate allows', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => true);

    $this->getJson('/stickle/api/segments')->assertOk();
});

it('does not apply the guard to ingest, and its response never varies with the gate', function (): void {

    withoutStickleGate();
    $undefined = $this->postJson('/stickle/api/track', [])->status();

    Gate::define('viewStickle', fn ($user = null): bool => false);
    $denies = $this->postJson('/stickle/api/track', [])->status();

    Gate::define('viewStickle', fn ($user = null): bool => true);
    $allows = $this->postJson('/stickle/api/track', [])->status();

    expect($undefined)->toBe($denies)
        ->and($denies)->toBe($allows)
        ->and($undefined)->not->toBe(403);
});
