<?php

declare(strict_types=1);

namespace StickleApp\Core\Tests\Fixtures\DiscoveryModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use StickleApp\Core\Traits\StickleEntity;

/**
 * Fixture for ClassUtils discovery. Not a real tracked model -- it exists so
 * the scan has something to find directly under the scanned namespace.
 */
class TopLevel extends Model
{
    use HasFactory;
    use StickleEntity;
}
