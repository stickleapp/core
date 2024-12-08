<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;

class Base
{
    public FilterTest $test;

    final public function __construct(public FilterTarget $target) {}

    public static function __callStatic(string $method, array $arguments)
    {
        $targetClass = 'Dclaysmith\LaravelCascade\Filters\Targets\\'.ucfirst($method);

        if (! class_exists($targetClass)) {
            throw new \Exception("Target class $targetClass does not exist");
        }

        $target = new $targetClass(
            config('cascade.database.tablePrefix'),
            ...$arguments
        );

        return new static($target);
    }

    public function __call(string $method, array $arguments): self|FilterTest
    {
        if (method_exists($this->target, $method)) {
            if ($newTargetType = $this->target->$method(...$arguments)) {
                $this->target = $newTargetType;
            }
        } elseif (isset($this->test)) {
            if (method_exists($this->test, $method)) {
                $return = $this->test->$method(...$arguments);
            }
        } else {
            $testClass = 'Dclaysmith\LaravelCascade\Filters\Tests\\'.ucfirst($method);
            if (class_exists($testClass)) {
                $this->test = new $testClass(...$arguments);
            }
        }

        return $this;
    }

    public function apply(Builder $builder, ?string $operator = 'and'): Builder
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
