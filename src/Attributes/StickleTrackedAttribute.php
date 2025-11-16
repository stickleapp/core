<?php

declare(strict_types=1);

namespace StickleApp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class StickleTrackedAttribute
{
    /**
     * Marks a model property or accessor method as tracked.
     * The value of this attribute will be recorded periodically
     * (default: every 6 hours) for analytics.
     */
    public function __construct() {}
}
