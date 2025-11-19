<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StickleApp\Core\Attributes\StickleAttributeMetadata;
use StickleApp\Core\Attributes\StickleObservedAttribute;
use StickleApp\Core\Attributes\StickleRelationshipMetadata;
use StickleApp\Core\Attributes\StickleTrackedAttribute;
use StickleApp\Core\Enums\ChartType;
use StickleApp\Core\Enums\DataType;
use StickleApp\Core\Enums\PrimaryAggregate;
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

    public function stickleLabel(): string
    {
        return $this->name;
    }

    // public static function stickleTrackedAggregates(): array {}
    // #[StickleAggregateMetadata([])
    /**
     * @return HasMany<Customer, $this>
     */
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
     *
     * @return BelongsTo<Customer, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    #[StickleRelationshipMetadata([
        'label' => 'Users of this Customer',
        'description' => 'The users that belong to this customer.',
    ])]
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function endUsers(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('user_type', UserType::END_USER);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function agents(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('user_type', UserType::AGENT);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('user_type', UserType::ADMIN);
    }

    #[StickleTrackedAttribute]
    #[StickleAttributeMetadata([
        'label' => 'Ticket Count',
        'description' => 'The total number of tickets for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function ticketCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tickets_count ?? $this->tickets()->count()
        );
    }

    #[StickleTrackedAttribute]
    #[StickleAttributeMetadata([
        'label' => 'Open Ticket Count',
        'description' => 'The total number of open tickets for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function openTicketCount(): Attribute
    {
        return Attribute::make(get: fn () => $this->tickets()
            ->whereStatus('open')
            ->count());
    }

    #[StickleTrackedAttribute]
    #[StickleAttributeMetadata([
        'label' => 'Tickets Closed',
        'description' => 'The total number of tickets closed.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function ticketsClosed(): Attribute
    {
        return Attribute::make(get: fn () => $this->tickets()
            ->whereStatus('resolved')
            ->count());
    }

    #[StickleTrackedAttribute]
    #[StickleAttributeMetadata([
        'label' => 'Tickets Closed (Last 30 Days)',
        'description' => 'The total number of tickets closed by the customer in the last 30 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function ticketsClosedLast30Days(): Attribute
    {
        return Attribute::make(get: fn () => $this->tickets()
            ->whereStatus('resolved')
            ->where('resolved_at', '>=', now()->subDays(30))
            ->count());
    }

    #[StickleAttributeMetadata([
        'label' => 'Tickets Closed (Last 7 Days)',
        'description' => 'The total number of tickets closed by the customer in the last 7 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function ticketsClosedLast7Days(): Attribute
    {
        return Attribute::make(get: fn () => $this->tickets()
            ->whereStatus('resolved')
            ->where('resolved_at', '>=', now()->subDays(7))
            ->count());
    }

    #[StickleTrackedAttribute]
    #[StickleAttributeMetadata([
        'label' => 'Average Resolution Time (All-time)',
        'description' => 'The average resolution time for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function averageResolutionTime(): Attribute
    {
        return Attribute::make(get: fn () => $this->tickets()
            ->whereStatus('resolved')
            ->avg('resolved_in_seconds'));
    }

    #[StickleTrackedAttribute]
    #[StickleAttributeMetadata([
        'label' => 'Average Ticket Resolution Time (Last 30 Days)',
        'description' => 'The average resolution time for the customer in the last 30 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function averageResolutionTime30Days(): Attribute
    {
        return Attribute::make(get: fn () => $this->tickets()
            ->whereStatus('resolved')
            ->where('resolved_at', '>=', now()->subDays(30))
            ->avg('resolved_in_seconds'));
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Ticket Resolution Time (Last 7 Days)',
        'description' => 'The average resolution time for the customer in the last 7 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function averageResolutionTime7Days(): Attribute
    {
        return Attribute::make(get: fn () => $this->tickets()
            ->whereStatus('resolved')
            ->where('resolved_at', '>=', now()->subDays(7))
            ->avg('resolved_in_seconds'));
    }

    /**
     * Their current active subscription plan.
     */
    protected function plan(): Attribute
    {
        return Attribute::make(get: fn () => $this->subscriptions()
            ->where('stripe_status', 'active')
            ->latest()
            ->first()
            ?->plan
            ?? '');
    }

    #[StickleObservedAttribute]
    #[StickleTrackedAttribute]
    #[StickleAttributeMetadata([
        'label' => 'Monthly Recurring Revenue',
        'description' => 'The total monthly recurring revenue for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::CURRENCY,
        'primaryAggregate' => PrimaryAggregate::SUM,
    ])]
    protected function mrr(): Attribute
    {
        return Attribute::make(get: fn (): int => match ($this->plan) {
            'basic' => 49,
            'pro' => 99,
            'enterprise' => 199,
            default => 0,
        });
    }

    // #[StickleAggregateMetadata([
    //     'label' => 'Quarterly Sales Volume',
    //     'description' => 'Total sales by quater.',
    //     'period' => Period::QUARTER,
    // ])]
    // public function getQuarterlySalesVolumeAttribute(): ?float
    // {
    //     return [
    //         'period_type' => Period::QUARTER,
    //         'period_name' => // previous quarter....
    //         'value' => // sum of previous quarter....
    //     ]
    // }
    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
