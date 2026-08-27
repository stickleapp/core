<?php

declare(strict_types=1);

namespace StickleApp\Core\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use StickleApp\Core\Models\ModelAttributes;

/**
 * The surface a model gains from the StickleEntity trait.
 *
 * This exists to give the analyser a name for something the runtime already
 * checks. Components accept a model as `object` and guard it with
 * ClassUtils::usesTrait(...), which tells PHP everything and PHPStan nothing,
 * so every call through it was method.notFound.
 *
 * A model is NOT required to declare `implements StickleTrackableContract`.
 * The runtime guard tests for the trait, and a model that uses the trait
 * satisfies this surface whether or not it names it. Type-hinting the
 * interface on the components would check something different from what the
 * guard checks -- a model could satisfy one and fail the other -- so the
 * components narrow to it in a docblock instead.
 */
interface StickleTrackableContract
{
    public function stickleLabel(): string;

    public function stickleUrl(): string;

    public function stickleAttribute(string $attribute): mixed;

    /**
     * The trait declares this as HasOne<ModelAttributes, $this>. An interface
     * cannot say $this here: the generic requires a Model, and an interface is
     * not known to be one. Model is the honest widening.
     *
     * @return HasOne<ModelAttributes, Model>
     */
    public function modelAttributes(): HasOne;

    /**
     * @param  array<int, string>|null  $relations
     * @return Collection<int, mixed>
     */
    public function stickleRelationships(?array $relations = []): Collection;

    /**
     * @return array<int, string>
     */
    public static function stickleTrackedAttributes(): array;

    /**
     * @return array<int, string>
     */
    public static function stickleObservedAttributes(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getStickleChartData(): array;
}
