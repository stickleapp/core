<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workbench\Database\Factories\SubscriptionItemFactory;

class SubscriptionItem extends Model
{
    use HasFactory;

    /**
     * Customer the order belongs to.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return SubscriptionItemFactory::new();
    }
}
