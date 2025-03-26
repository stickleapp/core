<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use StickleApp\Core\Attributes\StickleAttributeMetadata;
use StickleApp\Core\Enums\ChartType;
use StickleApp\Core\Enums\DataType;
use StickleApp\Core\Enums\PrimaryAggregate;
use StickleApp\Core\Traits\StickleEntity;
use Workbench\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, StickleEntity;

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
     * Specify the attributes that should be observed for changes
     */
    public static array $stickleObservedAttributes = [
        'user_rating',
    ];

    /**
     * Specify the attributes that should be tracked over time
     */
    public static array $stickleTrackedAttributes = [
        'user_rating',
    ];

    #[StickleAttributeMetadata([
        'label' => 'User Star Rating',
        'description' => 'The 1 to 5 star rating of the user.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getUserRatingAttribute($value): ?int
    {
        return $value;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
