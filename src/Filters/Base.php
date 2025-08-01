<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class Base
{
    public ?FilterTestContract $test;

    public ?FilterTargetContract $target = null;

    public ?string $targetClass;

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

        if (! class_exists($targetClass)) {
            throw new \Exception("Target class $targetClass does not exist");
        }

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
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    private function createTarget(Builder $builder): FilterTargetContract
    {
        if (! isset($this->targetClass)) {
            throw new \Exception('No target class defined');
        }

        $baseTargetClass = method_exists($this->targetClass, 'baseTarget')
            ? $this->targetClass::baseTarget()
            : $this->targetClass;

        if (method_exists($baseTargetClass, 'getTargetInstance')) {
            $target = $baseTargetClass::getTargetInstance(
                config('stickle.database.tablePrefix'),
                $builder,
                $this->targetArguments
            );

            if (! $target instanceof FilterTargetContract) {
                throw new \Exception('Target instance must implement FilterTargetContract');
            }

            return $target;
        }

        // For simple targets, instantiate directly with prefix and arguments
        $target = new $baseTargetClass(
            config('stickle.database.tablePrefix'),
            $builder,
            ...$this->targetArguments
        );

        if (! $target instanceof FilterTargetContract) {
            throw new \Exception('Target instance must implement FilterTargetContract');
        }

        return $target;
    }

    /**
     * @param  Builder<Model>  $builder
     * @return Builder<Model> $builder
     */
    public function apply(Builder $builder, string $operator): Builder
    {
        if (! isset($this->test)) {
            throw new \Exception('No test defined');
        }

        $target = $this->getTarget($builder);

        $target->applyJoin();

        return $this->test->applyFilter(
            $target->builder,
            $target,
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
