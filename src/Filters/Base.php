<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Base
{
    public FilterTest $test;

    final public function __construct(public FilterTarget $target) {}

    /**
     * This handles the first call to Filter::targetName()
     *
     * It creates a new instance of the target class and returns an instance of this Base class.
     *
     * @param  array<mixed>  $arguments
     */
    public static function __callStatic(string $method, array $arguments): Base
    {

        $targetClass = 'Dclaysmith\LaravelCascade\Filters\Targets\\'.ucfirst($method);

        if (! class_exists($targetClass)) {
            throw new \Exception("Target class $targetClass does not exist");
        }

        /** @var FilterTarget */
        $target = new $targetClass(
            config('cascade.database.tablePrefix'),
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

        if (method_exists($this->target, $method)) {
            if ($newTargetType = $this->target->$method(...$arguments)) {
                $this->target = $newTargetType;
            }
        } elseif (isset($this->test)) {
            if (method_exists($this->test, $method)) {
                $this->test->$method(...$arguments);
            }
        } else {
            $testClass = 'Dclaysmith\LaravelCascade\Filters\Tests\\'.ucfirst($method);
            if (class_exists($testClass)) {
                /** @var FilterTest */
                $test = new $testClass(...$arguments);
                $this->test = $test;
            }
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

        if (! isset($this->target)) {
            throw new \Exception('No target defined');
        }

        return $this->test->applyFilter(
            $this->target->applyJoin($builder),
            $this->target,
            $operator
        );
    }
}
