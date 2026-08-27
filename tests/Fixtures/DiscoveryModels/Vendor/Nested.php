<?php

declare(strict_types=1);

namespace StickleApp\Core\Tests\Fixtures\DiscoveryModels\Vendor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use StickleApp\Core\Traits\StickleEntity;

/**
 * Fixture for ClassUtils discovery, one namespace below the scanned root.
 * Nothing in the workbench sits in a sub-namespace, which is why this went
 * unnoticed.
 */
class Nested extends Model
{
    use HasFactory;
    use StickleEntity;
}
