<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use StickleApp\Core\Traits\StickleEntity;
use Workbench\Database\Factories\CustomerFactory;

class Customer extends Model
{
    use HasFactory, StickleEntity;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CustomerFactory::new();
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
    public array $stickleObservedAttributes = [
        'order_count',
        'order_item_count',
    ];

    /**
     * Specify the attributes that should be observed (via Observable)
     */
    public array $stickleTrackedAttributes = [
        'order_count',
        'order_item_count',
    ];

    public function children(): hasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent(): hasMany
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function users(): BelongsToMany
    {
        return $this->hasMany(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Order::class);
    }

    public function getOrderCountAttribute(): int
    {
        return $this->orders()->count();
    }

    public function getOrderItemCountAttribute(): int
    {
        return $this->orderItems()->count();
    }
}
