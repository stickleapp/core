<?php

namespace Dclaysmith\LaravelCascade\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Description
{
    public function __construct(public string $value) {}
}
