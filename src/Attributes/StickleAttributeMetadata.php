<?php

declare(strict_types=1);

namespace StickleApp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class StickleAttributeMetadata
{
    public function __construct(public array $value) {}
}
