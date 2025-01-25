<?php

namespace StickleApp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SegmentName
{
    public function __construct(public string $value) {}
}
