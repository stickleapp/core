<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StickleApp\Core\Traits\StickleEntity;
use Workbench\App\Enums\UserType;
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
    public static array $stickleObservedAttributes = [
        'mrr',
    ];

    /**
     * Specify the attributes that should be observed (via Observable)
     */
    public static array $stickleTrackedAttributes = [
        'mrr',
        'ticket_count',
        'open_ticket_count',
        'closed_ticket_count',
        'tickets_closed_last_30_days',
        'average_resolution_time',
        'average_resolution_time_30_days',
    ];

    #[StickleRelationshipMetadata([
        'label' => 'Child Customers',
        'description' => 'The child accounts of the customer.',
    ])]
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Parent account in a parent <> child relationship.
     *
     * For instance: Microsoft EU may have Microsoft as a parent account
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    #[StickleRelationshipMetadata([
        'label' => 'Users of this Customer',
        'description' => 'The users that belong to this customer.',
    ])]
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function endUsers(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('user_type', UserType::END_USER);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('user_type', UserType::AGENT);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('user_type', UserType::ADMIN);
    }

    #[StickleAttributeMetadata([
        'label' => 'Ticket Count',
        'description' => 'The total number of tickets for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getTicketCountAttribute(): int
    {
        return $this->tickets()->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Open Ticket Count',
        'description' => 'The total number of open tickets for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getOpenTicketCountAttribute(): int
    {
        return $this->tickets()
            ->whereStatus('open')
            ->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Tickets Closed',
        'description' => 'The total number of tickets closed.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getTicketsClosedAttribute(): int
    {
        return $this->tickets()
            ->whereStatus('resolved')
            ->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Tickets Closed (Last 30 Days)',
        'description' => 'The total number of tickets closed by the customer in the last 30 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getTicketsClosedLast30DaysAttribute(): int
    {
        return $this->tickets()
            ->whereStatus('resolved')
            ->where('resolved_at', '>=', now()->subDays(30))
            ->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Tickets Closed (Last 7 Days)',
        'description' => 'The total number of tickets closed by the customer in the last 7 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getTicketsClosedLast7DaysAttribute(): int
    {
        return $this->tickets()
            ->whereStatus('resolved')
            ->where('resolved_at', '>=', now()->subDays(7))
            ->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Resolution Time (All-time)',
        'description' => 'The average resolution time for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getAverageResolutionTimeAttribute(): ?float
    {
        return $this->tickets()
            ->whereStatus('resolved')
            ->avg('resolved_in_seconds');
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Ticket Resolution Time (Last 30 Days)',
        'description' => 'The average resolution time for the customer in the last 30 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getAverageResolutionTime30DaysAttribute(): ?float
    {
        return $this->tickets()
            ->whereStatus('resolved')
            ->where('resolved_at', '>=', now()->subDays(30))
            ->avg('resolved_in_seconds');
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Ticket Resolution Time (Last 7 Days)',
        'description' => 'The average resolution time for the customer in the last 7 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getAverageResolutionTime7DaysAttribute(): ?float
    {
        return $this->tickets()
            ->whereStatus('resolved')
            ->where('resolved_at', '>=', now()->subDays(7))
            ->avg('resolved_in_seconds');
    }

    /**
     * Their current active subscription plan.
     */
    public function getPlanAttribute(): string
    {
        return $this->subscriptions()
            ->where('stripe_status', 'active')
            ->latest()
            ->first()
            ?->plan
            ?? '';
    }

    #[StickleAttributeMetadata([
        'label' => 'Monthly Recurring Revenue',
        'description' => 'The total monthly recurring revenue for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::CURRENCY,
        'primaryAggregate' => PrimaryAggregate::SUM,
    ])]
    public function getMrrAttribute(): ?float
    {
        return match ($this->plan) {
            'basic' => 49,
            'pro' => 99,
            'enterprise' => 199,
            default => 0,
        };
    }
}
