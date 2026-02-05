<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use StickleApp\Core\Attributes\StickleAttributeMetadata;
use StickleApp\Core\Enums\ChartType;
use StickleApp\Core\Enums\DataType;
use StickleApp\Core\Enums\PrimaryAggregate;
use StickleApp\Core\Traits\StickleEntity;
use Workbench\App\Enums\UserType;
use Workbench\Workbench\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, StickleEntity;

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
        'user_type',
        'customer_id',
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

    public static function stickleObservedAttributes(): array
    {
        return ['user_level'];
    }

    public static function stickleTrackedAttributes(): array
    {
        return [
            'user_rating',
            'ticket_count',
            'open_ticket_count',
            'resolved_ticket_count',
            'tickets_resolved_last_7_days',
            'tickets_resolved_last_30_days',
            'average_resolution_time',
            'average_resolution_time_7_days',
            'average_resolution_time_30_days',
        ];
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function ticketsAssigned(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to_id');
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function ticketsCreated(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by_id');
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function ticketsResolved(): HasMany
    {
        return $this->hasMany(Ticket::class, 'resolved_by_id');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    #[StickleAttributeMetadata([
        'label' => 'User Rating',
        'description' => 'The 1 to 5 star rating of the user.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function userRating(): Attribute
    {
        return Attribute::make(get: fn ($value) => $this->ticketsResolved()
            ->avg('rating'));
    }

    #[StickleAttributeMetadata([
        'label' => 'Ticket Count',
        'description' => 'The total number of tickets for the user.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function ticketCount(): Attribute
    {
        return Attribute::make(get: fn () => $this->ticketsAssigned()->count());
    }

    #[StickleAttributeMetadata([
        'label' => 'Open Ticket Count',
        'description' => 'The total number of open tickets for the user.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function openTicketCount(): Attribute
    {
        return Attribute::make(get: fn () => $this->ticketsAssigned()
            ->whereStatus('open')
            ->count());
    }

    #[StickleAttributeMetadata([
        'label' => 'Resolved Ticket Count',
        'description' => 'The total number of resolved tickets for the user.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function resolvedTicketCount(): Attribute
    {
        return Attribute::make(get: fn () => $this->ticketsResolved()
            ->count());
    }

    #[StickleAttributeMetadata([
        'label' => 'Tickets Resolved (Last 7 Days)',
        'description' => 'The total number of tickets closed by the customer in the last 7 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function ticketsResolvedLast7Days(): Attribute
    {
        return Attribute::make(get: fn () => $this->ticketsResolved()
            ->where('resolved_at', '>=', now()->subDays(7))
            ->count());
    }

    #[StickleAttributeMetadata([
        'label' => 'Tickets Resolved (Last 30 Days)',
        'description' => 'The total number of tickets closed by the customer in the last 30 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function ticketsResolvedLast30Days(): Attribute
    {
        return Attribute::make(get: fn () => $this->ticketsResolved()
            ->where('resolved_at', '>=', now()->subDays(30))
            ->count());
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Resolution Time',
        'description' => 'The average resolution time for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function averageResolutionTime(): Attribute
    {
        return Attribute::make(get: fn () => $this->ticketsResolved()
            ->avg('resolved_in_seconds'));
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Resolution Time Last 7 Days',
        'description' => 'The average resolution time for the customer in the last 7 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function averageResolutionTime7Days(): Attribute
    {
        return Attribute::make(get: fn () => $this->ticketsResolved()
            ->where('resolved_at', '>=', now()->subDays(7))
            ->avg('resolved_in_seconds'));
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Resolution Time Last 30 Days',
        'description' => 'The average resolution time for the customer in the last 30 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    protected function averageResolutionTime30Days(): Attribute
    {
        return Attribute::make(get: fn () => $this->ticketsResolved()
            ->where('resolved_at', '>=', now()->subDays(30))
            ->avg('resolved_in_seconds'));
    }

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
            'user_type' => UserType::class,
        ];
    }
}
