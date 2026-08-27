<?php

declare(strict_types=1);

namespace StickleApp\Core\Tests\Fixtures\CollidingModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use StickleApp\Core\Traits\StickleEntity;

/** Fixture: collides on basename with CollidingModels\Vendor\Thing. */
class Thing extends Model
{
    use HasFactory;
    use StickleEntity;
}
