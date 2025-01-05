<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Dclaysmith\LaravelCascade\Attributes\Description;
use Dclaysmith\LaravelCascade\Traits\Trackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Workbench\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Trackable;

    protected $table = 'users';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Specify the attributes that should be observed (via Observable)
     */
    public array $observedAttributes = [
        'user_rating',
        'order_count',
        'order_item_count',
        // 'total_spend',
        // 'last_order_total',
        // 'last_order_item_count',
    ];

    /**
     * Users orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Order::class);
    }

    #[Description('How many orders has this user placed?')]
    public function getOrderCountAttribute(): int
    {
        return $this->orders()->count();
    }

    #[Description('How many items has this user purchased?')]
    public function getOrderItemCountAttribute(): int
    {
        return $this->orderItems()->count();
    }

    public function getItemsPurchasedAttribute()
    {
        return $this->items_purchased;
    }
}
