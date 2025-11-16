<?php

declare(strict_types=1);

namespace StickleApp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class StickleObservedAttribute
{
    /**
     * Marks a model property or accessor method as observed.
     * When this attribute changes, an ObjectAttributeChanged event
     * will be dispatched immediately.
     */
    public function __construct() {}
}
