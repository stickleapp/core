<?php

declare(strict_types=1);

namespace StickleApp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class StickleSegmentMetadata
{
    public function __construct(public array $value) {}
}
