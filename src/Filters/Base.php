<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters;

use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

/**
 * The filter entry point. Both halves of its surface are magic, so both are
 * declared here or no consumer can analyse a segment written against it.
 *
 * Static calls resolve through __callStatic() to Targets\{Ucfirst}. The nine
 * derived targets (*Aggregate, *AggregateDelta, NumberDelta) are deliberately
 * absent: they are reached by way of baseTarget() from the targets below, and
 * calling one by name resolves it straight back to its base -- Filter::numberDelta('x')
 * yields a plain Number, not a delta -- so declaring them would advertise a
 * surface that does not behave as its name reads.
 *
 * Instance calls resolve through __call() to Tests\{Ucfirst}, and their
 * signatures are those classes' constructors.
 *
 * @method static self boolean(string $attribute)
 * @method static self date(string $attribute)
 * @method static self datetime(string $attribute)
 * @method static self number(string $attribute)
 * @method static self text(string $attribute)
 * @method static self segment(string $segmentIdentifier)
 * @method static self segmentHistory(string $segmentIdentifier)
 * @method static self eventCount(string $event)
 * @method static self requestCount(?string $url = null)
 * @method static self sessionCount()
 * @method self beginsWith(string $comparator, bool $caseSensitive = false)
 * @method self between(mixed $start, mixed $end)
 * @method self contains(string $comparator, bool $caseSensitive = false)
 * @method self equals(mixed $comparator)
 * @method self equalsColumn(string $comparator)
 * @method self greaterThan(mixed $comparator)
 * @method self greaterThanColumn(string $comparator)
 * @method self greaterThanOrEqualTo(mixed $comparator)
 * @method self greaterThanOrEqualToColumn(string $comparator)
 * @method self hasBeenInSegment()
 * @method self hasNeverBeenInSegment()
 * @method self isAfter(DateTimeInterface|string|Number|int $comparator)
 * @method self isBefore(DateTimeInterface|string|Number|int $comparator)
 * @method self isFalse()
 * @method self isInSegment()
 * @method self isNotFalse()
 * @method self isNotInSegment()
 * @method self isNotNull()
 * @method self isNotTrue()
 * @method self isNull()
 * @method self isTrue()
 * @method self lessThan(mixed $comparator)
 * @method self lessThanColumn(string $comparator)
 * @method self lessThanOrEqualTo(mixed $comparator)
 * @method self lessThanOrEqualToColumn(string $comparator)
 * @method self notEquals(mixed $comparator)
 * @method self notEqualsColumn(string $comparator)
 * @method self occurredAfter(mixed $comparator)
 * @method self occurredBefore(mixed $comparator)
 * @method self willOccurAfter(mixed $comparator)
 * @method self willOccurBefore(mixed $comparator)
 */
class Base
{
    public ?FilterTestContract $test = null;

    public ?FilterTargetContract $target = null;

    public ?string $targetClass = null;

    /** @var array<mixed> */
    public ?array $targetArguments = [];

    /**
     * This handles the first call to Filter::targetName()
     *
     * It creates a new instance of the target class and returns an instance of this Base class.
     *
     * @param  array<mixed>  $arguments
     */
    public static function __callStatic(string $method, array $arguments): Base
    {

        $targetClass = 'StickleApp\Core\Filters\Targets\\'.ucfirst($method);

        throw_unless(class_exists($targetClass), Exception::class, "Target class $targetClass does not exist");

        $filter = new self;

        $filter->targetClass = $targetClass;

        $filter->targetArguments = $arguments;

        return $filter;
    }

    /**
     * The Base class doesn't do anything except apply the filter to the builder.
     * the __call method is used to call methods on the target and test classes.
     *
     * This is a fluent interface, so it returns $this.
     *
     * @param  array<mixed>  $arguments
     */
    public function __call(string $method, array $arguments): self
    {
        $testClass = 'StickleApp\Core\Filters\Tests\\'.ucfirst($method);

        if (class_exists($testClass)) {
            /** @var FilterTestContract */
            $test = new $testClass(...$arguments);
            $this->test = $test;
        }

        return $this;
    }

    // /**
    //  * Magic getter to lazily create target when accessed
    //  */
    // public function __get(string $name): mixed
    // {
    //     if ($name === 'target') {
    //         throw new \Exception('Target property cannot be accessed directly. Use getTarget() method with a Builder instance.');
    //     }
    //     throw new \Exception("Property {$name} does not exist");
    // }
    /**
     * Create target instance based on target class type
     *
     * @param  Builder<Model>  $builder
     */
    private function createTarget(Builder $builder): FilterTargetContract
    {
        throw_unless($this->targetClass !== null, Exception::class, 'No target class defined');

        $baseTargetClass = method_exists($this->targetClass, 'baseTarget')
            ? $this->targetClass::baseTarget()
            : $this->targetClass;

        if (method_exists($baseTargetClass, 'getTargetInstance')) {
            $target = $baseTargetClass::getTargetInstance(
                config('stickle.database.tablePrefix'),
                $builder,
                $this->targetArguments
            );

            throw_unless($target instanceof FilterTargetContract, Exception::class, 'Target instance must implement FilterTargetContract');

            return $target;
        }

        // For simple targets, instantiate directly with prefix and arguments
        $target = new $baseTargetClass(
            config('stickle.database.tablePrefix'),
            $builder,
            ...$this->targetArguments
        );

        throw_unless($target instanceof FilterTargetContract, Exception::class, 'Target instance must implement FilterTargetContract');

        return $target;
    }

    /**
     * @param  Builder<Model>  $builder
     * @return Builder<Model> $builder
     */
    public function apply(Builder $builder, string $operator): Builder
    {
        throw_unless($this->test instanceof FilterTestContract, Exception::class, 'No test defined');

        $filterTargetContract = $this->getTarget($builder);

        $filterTargetContract->applyJoin();

        return $this->test->applyFilter(
            $filterTargetContract->builder,
            $filterTargetContract,
            $operator
        );
    }

    /**
     * @param  Builder<Model>  $builder
     */
    public function getTarget(Builder $builder): FilterTargetContract
    {
        // Always recreate target with current arguments in case they've changed
        $this->target = $this->createTarget($builder);

        return $this->target;
    }

    public function increased(): self
    {
        $this->targetArguments['deltaVerb'] = 'increased';

        return $this;
    }

    public function decreased(): self
    {
        $this->targetArguments['deltaVerb'] = 'decreased';

        return $this;
    }

    public function changed(): self
    {
        $this->targetArguments['deltaVerb'] = 'changed';

        return $this;
    }

    public function avg(): self
    {
        $this->targetArguments['aggregate'] = 'avg';

        return $this;
    }

    public function sum(): self
    {
        $this->targetArguments['aggregate'] = 'sum';

        return $this;
    }

    public function min(): self
    {
        $this->targetArguments['aggregate'] = 'min';

        return $this;
    }

    public function max(): self
    {
        $this->targetArguments['aggregate'] = 'max';

        return $this;
    }

    public function count(): self
    {
        $this->targetArguments['aggregate'] = 'count';

        return $this;
    }

    /**
     * @param  array<DateTimeInterface>  $compareToDateRange
     * @param  array<DateTimeInterface>  $currentDateRange
     */
    public function betweenDateRanges(array $compareToDateRange, array $currentDateRange): self
    {
        $this->targetArguments['currentDateRange'] = $currentDateRange;

        $this->targetArguments['compareToDateRange'] = $compareToDateRange;

        return $this;
    }

    public function betweenDates(DateTimeInterface $startDate, DateTimeInterface $endDate): self
    {

        $this->targetArguments['currentDateRange'] = [$startDate, $endDate];

        return $this;
    }
}
