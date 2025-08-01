<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Contracts\FilterTargetContract;

class EventCount extends FilterTargetContract
{
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
            'event' => data_get($arguments, 'event', data_get($arguments, '0', null)),
            'aggregate' => data_get($arguments, 'aggregate'),
        ];
    }

    private static function validateEvent(?string $event): void
    {
        if (! $event) {
            throw new \InvalidArgumentException('Event is required for EventCount filter targets. You should have passed `event` as a paramter of your target (ex. Filter::eventCount(\'clicked:something\')).');
        }
    }

    private static function validateAggregate(?string $aggregate): void
    {
        if (! $aggregate) {
            throw new \InvalidArgumentException('Aggregate is required for EventCount filter targets. Did you call sum(), avg(), min(), max(), or count()?');
        }
    }

    /**
     * @param  array<mixed>  $currentDateRange
     * @param  array<mixed>  $compareToDateRange
     */
    private static function validateDateRanges(array $currentDateRange, array $compareToDateRange, bool $hasDelta): void
    {
        if (count($currentDateRange) !== 2) {
            throw new \InvalidArgumentException('Current DateRange is required. Did you call betweenDates() or betweenDateRanges() on your filter?');
        }

        if ($hasDelta && count($compareToDateRange) !== 2) {
            throw new \InvalidArgumentException('Delta type (increased, decreased, changed) is specified but no $compareToDateRange is specified. Did you call betweenDateRanges() on your filter.');
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
            throw new \InvalidArgumentException('A `$compareToDateRange` is provided but no delta type (increased, decreased, changed) is specified. Call increased(), decreased(), or changed().');
        }
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<mixed>  $currentDateRange
     * @param  array<mixed>  $compareToDateRange
     */
    private static function createEventCountAggregateDelta(?string $prefix, Builder $builder, string $event, string $aggregate, string $deltaVerb, array $currentDateRange, array $compareToDateRange): EventCountAggregateDelta
    {
        return new EventCountAggregateDelta($prefix, $builder, $event, $aggregate, $deltaVerb, $currentDateRange, $compareToDateRange);
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<mixed>  $currentDateRange
     */
    private static function createEventCountAggregate(?string $prefix, Builder $builder, string $event, string $aggregate, array $currentDateRange): EventCountAggregate
    {
        return new EventCountAggregate($prefix, $builder, $event, $aggregate, $currentDateRange[0], $currentDateRange[1] ?? now());
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<string, mixed>  $arguments
     */
    public static function getTargetInstance(?string $prefix, Builder $builder, array $arguments): FilterTargetContract
    {
        $params = self::parseArguments($arguments);

        self::validateEvent($params['event']);
        self::validateAggregate($params['aggregate']);
        self::validateDateRanges($params['currentDateRange'], $params['compareToDateRange'], (bool) $params['deltaVerb']);
        self::validateDeltaConfiguration($params['deltaVerb'], $params['compareToDateRange']);

        if ($params['deltaVerb']) {
            return self::createEventCountAggregateDelta($prefix, $builder, $params['event'], $params['aggregate'], $params['deltaVerb'], $params['currentDateRange'], $params['compareToDateRange']);
        }

        return self::createEventCountAggregate($prefix, $builder, $params['event'], $params['aggregate'], $params['currentDateRange']);
    }
}
