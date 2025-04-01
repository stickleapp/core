<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        'user_type',
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
        'user_type' => UserType::class,
    ];

    /**
     * Specify the attributes that should be observed for changes
     */
    public static array $stickleObservedAttributes = [

    ];

    /**
     * Specify the attributes that should be tracked over time
     */
    public static array $stickleTrackedAttributes = [
        'user_rating',
        'ticket_count',
        'open_ticket_count',
        'closed_ticket_count',
        'tickets_closed_last_30_days',
        'average_resolution_time',
        'average_resolution_time_30_days',
    ];

    public function ticketsAssigned(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to_id');
    }

    public function ticketsCreated(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by_id');
    }

    public function ticketsResolved(): HasMany
    {
        return $this->hasMany(Ticket::class, 'resolved_by_id');
    }

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
    public function getUserRatingAttribute($value): ?int
    {
        return $this->ticketsResolved()
            ->avg('rating');
    }

    #[StickleAttributeMetadata([
        'label' => 'Ticket Count',
        'description' => 'The total number of tickets for the user.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getTicketCountAttribute(): int
    {
        return $this->ticketsAssigned()->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Open Ticket Count',
        'description' => 'The total number of open tickets for the user.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getOpenTicketCountAttribute(): int
    {
        return $this->ticketsAssigned()
            ->whereStatus('open')
            ->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Resolved Ticket Count',
        'description' => 'The total number of resolved tickets for the user.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getResolvedTicketCountAttribute(): int
    {
        return $this->ticketsResolved()
            ->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Tickets Resolved (Last 7 Days)',
        'description' => 'The total number of tickets closed by the customer in the last 7 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getTicketsResolvedLast7DaysAttribute(): int
    {
        return $this->ticketsResolved()
            ->where('resolved_at', '>=', now()->subDays(7))
            ->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Tickets Resolved (Last 30 Days)',
        'description' => 'The total number of tickets closed by the customer in the last 30 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::INTEGER,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getTicketsResolvedLast30DaysAttribute(): int
    {
        return $this->ticketsResolved()
            ->where('resolved_at', '>=', now()->subDays(30))
            ->count();
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Resolution Time',
        'description' => 'The average resolution time for the customer.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getAverageResolutionTimeAttribute(): ?float
    {
        return $this->ticketsResolved()
            ->avg('resolved_in_seconds');
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Resolution Time Last 7 Days',
        'description' => 'The average resolution time for the customer in the last 7 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getAverageResolutionTime7DaysAttribute(): ?float
    {
        return $this->ticketsResolved()
            ->where('resolved_at', '>=', now()->subDays(7))
            ->avg('resolved_in_seconds');
    }

    #[StickleAttributeMetadata([
        'label' => 'Average Resolution Time Last 30 Days',
        'description' => 'The average resolution time for the customer in the last 30 days.',
        'chartType' => ChartType::LINE,
        'dataType' => DataType::TIME,
        'primaryAggregate' => PrimaryAggregate::AVG,
    ])]
    public function getAverageResolutionTime30DaysAttribute(): ?float
    {
        return $this->ticketsResolved()
            ->where('resolved_at', '>=', now()->subDays(30))
            ->avg('resolved_in_seconds');
    }
}
