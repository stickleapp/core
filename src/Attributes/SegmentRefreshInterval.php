<?php

namespace StickleApp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SegmentRefreshInterval
{
    public function __construct(public int $value)
    {
    }
}
