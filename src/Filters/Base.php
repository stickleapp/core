<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class Base
{
    public FilterTestContract $test;

    final public function __construct(public FilterTargetContract $target) {}

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

        /** @var FilterTargetContract */
        $target = new $targetClass(
            config('stickle.database.tablePrefix'),
            ...$arguments
        );

        return new static($target);
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

        /**
         * HasDeltaFilters (increased, decreased) will changge the `Target`
         * from EventCount to EventCountDelta
         */
        if (method_exists($this->target, $method)) {
            if ($newTargetType = $this->target->$method(...$arguments)) {
                $this->target = $newTargetType;
            }
            /**
             * I'm not sure what this is for..  "between"
             */
        } elseif (isset($this->test)) {
            if (method_exists($this->test, $method)) {
                $this->test->$method(...$arguments);
            }
        } elseif (class_exists($testClass)) {
            /** @var FilterTestContract */
            $test = new $testClass(...$arguments);
            $this->test = $test;
        } else {
            throw new \Exception("Method `$method()` cannot be called via __call. Does it exist on the target or test classes?");
        }

        return $this;
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

        // if (! isset($this->target)) {
        //     throw new \Exception('No target defined');
        // }

        return $this->test->applyFilter(
            $this->target->applyJoin($builder),
            $this->target,
            $operator
        );
    }
}
