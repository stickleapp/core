<?php

declare(strict_types=1);

namespace StickleApp\Core\Tests\Fixtures\Attributes;

use StickleApp\Core\Attributes\StickleAttributeMetadata;

/** Fixture: carries StickleAttributeMetadata on a property rather than a method. */
class AnnotatedProperty
{
    #[StickleAttributeMetadata(['label' => 'Seat Count'])]
    public int $seat_count = 0;
}
