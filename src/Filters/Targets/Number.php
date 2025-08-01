<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Contracts\FilterTargetContract;

class Number extends FilterTargetContract
{
    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public Builder $builder,
        public string $attribute
    ) {}

    public function property(): ?string
    {
        return "data->'{$this->attribute}'";
    }

    public function castProperty(): mixed
    {
        return sprintf('(%s)::numeric', $this->property());
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private static function parseArguments(array $arguments): array
    {
        return [
            'deltaVerb' => data_get($arguments, 'deltaVerb', null),
            'currentDateRange' => data_get($arguments, 'currentDateRange', []),
            'compareToDateRange' => data_get($arguments, 'compareToDateRange', []),
            'attribute' => data_get($arguments, 'attribute', data_get($arguments, '0', null)),
            'aggregate' => data_get($arguments, 'aggregate'),
        ];
    }

    private static function validateAttribute(?string $attribute): void
    {
        if (! $attribute) {
            throw new \InvalidArgumentException('Attribute is required for Number filter targets. You should have passed an attribute as a parameter of your target (ex. Filter::Number(\'price\')).');
        }
    }

    /**
     * @param  array<mixed>  $currentDateRange
     * @param  array<mixed>  $compareToDateRange
     */
    private static function validateDateRanges(array $currentDateRange, array $compareToDateRange, bool $aggregate, bool $hasDelta): void
    {
        if ($aggregate && count($currentDateRange) !== 2) {
            throw new \InvalidArgumentException('Current date range is required when using aggregates. Did you call betweenDates() or betweenDateRanges() on your filter?');
        }

        if ($hasDelta && count($currentDateRange) !== 2) {
            throw new \InvalidArgumentException('Current date range is required when using delta comparisons. Did you call betweenDates() or betweenDateRanges() on your filter?');
        }

        if ($aggregate && $hasDelta && count($compareToDateRange) !== 2) {
            throw new \InvalidArgumentException('Compare-to date range is required when using delta comparisons with aggregates. Did you call betweenDateRanges() on your filter?');
        }
    }

    /**
     * @param  array<mixed>  $compareToDateRange
     */
    private static function validateDeltaConfiguration(?string $deltaVerb, array $compareToDateRange): void
    {
        if ($deltaVerb && count($compareToDateRange) !== 2) {
            throw new \InvalidArgumentException('Delta type (increased, decreased, changed) is specified but no compare-to date range is provided. Did you call betweenDateRanges() on your filter?');
        }

        if (! $deltaVerb && count($compareToDateRange) === 2) {
            throw new \InvalidArgumentException('A compare-to date range is provided but no delta type (increased, decreased, changed) is specified. Call increased(), decreased(), or changed().');
        }
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<mixed>  $currentDateRange
     * @param  array<mixed>  $compareToDateRange
     */
    private static function createNumberAggregateDelta(?string $prefix, Builder $builder, string $attribute, string $aggregate, string $deltaVerb, array $compareToDateRange, array $currentDateRange): NumberAggregateDelta
    {
        return new NumberAggregateDelta($prefix, $builder, $attribute, $aggregate, $deltaVerb, $compareToDateRange, $currentDateRange);
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<mixed>  $currentDateRange
     */
    private static function createNumberAggregate(?string $prefix, Builder $builder, string $attribute, string $aggregate, array $currentDateRange): NumberAggregate
    {
        return new NumberAggregate($prefix, $builder, $attribute, $aggregate, $currentDateRange[0], $currentDateRange[1] ?? now());
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<mixed>  $currentDateRange
     */
    private static function createNumberDelta(?string $prefix, Builder $builder, string $attribute, array $currentDateRange): NumberDelta
    {
        return new NumberDelta($prefix, $builder, $attribute, $currentDateRange[0], $currentDateRange[1] ?? null);
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    private static function createNumber(?string $prefix, Builder $builder, string $attribute): Number
    {
        return new Number($prefix, $builder, $attribute);
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<string, mixed>  $arguments
     */
    public static function getTargetInstance(?string $prefix, Builder $builder, array $arguments): FilterTargetContract
    {
        $params = self::parseArguments($arguments);

        self::validateAttribute($params['attribute']);
        self::validateDateRanges($params['currentDateRange'], $params['compareToDateRange'], (bool) $params['aggregate'], (bool) $params['deltaVerb']);
        self::validateDeltaConfiguration($params['deltaVerb'], $params['compareToDateRange']);

        if ($params['aggregate'] && $params['deltaVerb']) {
            return self::createNumberAggregateDelta($prefix, $builder, $params['attribute'], $params['aggregate'], $params['deltaVerb'], $params['compareToDateRange'], $params['currentDateRange']);
        }

        if ($params['aggregate']) {
            return self::createNumberAggregate($prefix, $builder, $params['attribute'], $params['aggregate'], $params['currentDateRange']);
        }

        if ($params['deltaVerb']) {
            return self::createNumberDelta($prefix, $builder, $params['attribute'], $params['currentDateRange']);
        }

        return self::createNumber($prefix, $builder, $params['attribute']);
    }
}
