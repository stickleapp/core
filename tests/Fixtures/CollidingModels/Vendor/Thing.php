<?php

declare(strict_types=1);

namespace StickleApp\Core\Tests\Fixtures\CollidingModels\Vendor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use StickleApp\Core\Traits\StickleEntity;

/** Fixture: collides on basename with CollidingModels\Thing. */
class Thing extends Model
{
    use HasFactory;
    use StickleEntity;
}
