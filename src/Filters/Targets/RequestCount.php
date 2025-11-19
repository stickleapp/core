<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use StickleApp\Core\Contracts\FilterTargetContract;

class RequestCount extends FilterTargetContract
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
            'url' => data_get($arguments, 'url', data_get($arguments, '0', null)),
            'aggregate' => data_get($arguments, 'aggregate'),
        ];
    }

    private static function validateUrl(): void
    {
        // throw_unless($url, InvalidArgumentException::class, 'URL is required for RequestCount filter targets. You should have passed `url` as a paramter of your target (ex. Filter::requestCount(\'/api/users\')).');
    }

    private static function validateAggregate(?string $aggregate): void
    {
        throw_unless($aggregate, InvalidArgumentException::class, 'Aggregate is required for RequestCount filter targets. Did you call sum(), avg(), min(), max(), or count()?');
    }

    /**
     * @param  array<mixed>  $currentDateRange
     * @param  array<mixed>  $compareToDateRange
     */
    private static function validateDateRanges(array $currentDateRange, array $compareToDateRange, bool $hasDelta): void
    {
        throw_if(count($currentDateRange) !== 2, InvalidArgumentException::class, 'Current DateRange is required. Did you call betweenDates() or betweenDateRanges() on your filter?');

        throw_if($hasDelta && count($compareToDateRange) !== 2, InvalidArgumentException::class, 'Delta type (increased, decreased, changed) is specified but no $compareToDateRange is specified. Did you call betweenDateRanges() on your filter.');
    }

    /**
     * @param  array<mixed>  $compareToDateRange
     */
    private static function validateDeltaConfiguration(?string $deltaVerb, array $compareToDateRange): void
    {
        throw_if($deltaVerb && count($compareToDateRange) !== 2, InvalidArgumentException::class, 'Delta type (increased, decreased, changed) is specified but no compare-to date range is provided. Did you call betweenDateRanges() on your filter?');

        throw_if(! $deltaVerb && count($compareToDateRange) === 2, InvalidArgumentException::class, 'A `$compareToDateRange` is provided but no delta type (increased, decreased, changed) is specified. Call increased(), decreased(), or changed().');
    }

    /**
     * @param  Builder<Model>  $builder
     * @param  array<mixed>  $currentDateRange
     * @param  array<mixed>  $compareToDateRange
     */
    private static function createRequestCountAggregateDelta(?string $prefix, Builder $builder, ?string $url, string $aggregate, string $deltaVerb, array $compareToDateRange, array $currentDateRange): RequestCountAggregateDelta
    {
        return new RequestCountAggregateDelta($prefix, $builder, $url, $aggregate, $deltaVerb, $currentDateRange, $compareToDateRange);
    }

    /**
     * @param  Builder<Model>  $builder
     * @param  array<mixed>  $currentDateRange
     */
    private static function createRequestCountAggregate(?string $prefix, Builder $builder, ?string $url, string $aggregate, array $currentDateRange): RequestCountAggregate
    {
        return new RequestCountAggregate($prefix, $builder, $url, $aggregate, $currentDateRange[0], $currentDateRange[1] ?? now());
    }

    /**
     * @param  Builder<Model>  $builder
     * @param  array<string, mixed>  $arguments
     */
    public static function getTargetInstance(?string $prefix, Builder $builder, array $arguments): FilterTargetContract
    {
        $params = self::parseArguments($arguments);

        self::validateUrl();
        self::validateAggregate($params['aggregate']);
        self::validateDateRanges($params['currentDateRange'], $params['compareToDateRange'], (bool) $params['deltaVerb']);
        self::validateDeltaConfiguration($params['deltaVerb'], $params['compareToDateRange']);

        if ($params['deltaVerb']) {
            return self::createRequestCountAggregateDelta(
                prefix: $prefix,
                builder: $builder,
                url: $params['url'],
                aggregate: $params['aggregate'],
                deltaVerb: $params['deltaVerb'],
                compareToDateRange: $params['compareToDateRange'],
                currentDateRange: $params['currentDateRange']);
        }

        return self::createRequestCountAggregate(
            prefix: $prefix,
            builder: $builder,
            url: $params['url'],
            aggregate: $params['aggregate'],
            currentDateRange: $params['currentDateRange']
        );
    }
}
