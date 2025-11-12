---
outline: deep
---

# Recipes

Common patterns and ready-to-use code examples for Stickle. Copy, paste, and adapt these recipes to your application.

## Track Monthly Recurring Revenue (MRR)

Track MRR as a calculated attribute that updates automatically:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use StickleApp\Core\Traits\StickleEntity;

class Account extends Model
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'mrr',
            'plan_name',
            'subscription_count',
        ];
    }

    protected function mrr(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->subscriptions()
                ->where('status', 'active')
                ->sum('monthly_amount')
        );
    }

    protected function subscriptionCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->subscriptions()
                ->where('status', 'active')
                ->count()
        );
    }
}
```

**Create a high-MRR segment:**

```php
<?php

namespace App\Segments;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Contracts\Segment;
use StickleApp\Core\Filters\Filter;

class HighMRRAccounts extends Segment
{
    public string $model = Account::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->stickleWhere(
                Filter::number('mrr')->greaterThan(5000)
            );
    }
}
```

---

## Identify Churning Customers

Find customers who are showing signs of churn:

```php
<?php

namespace App\Segments;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\Segment;
use StickleApp\Core\Filters\Filter;

#[StickleSegmentMetadata([
    'name' => 'At Risk of Churning',
    'description' => 'Paying customers with declining engagement',
    'exportInterval' => 60, // Check hourly
])]
class AtRiskCustomers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            // Active subscription
            ->where('subscription_status', 'active')
            // Low recent activity
            ->stickleWhere(
                Filter::sessionCount()
                    ->count()
                    ->betweenDates(
                        startDate: now()->subDays(30),
                        endDate: now()
                    )
                    ->lessThan(3)
            )
            // Declining page views
            ->stickleWhere(
                Filter::eventCount('page_view')
                    ->count()
                    ->decreased()
                    ->betweenDateRanges(
                        compareToDateRange: [now()->subDays(60), now()->subDays(30)],
                        currentDateRange: [now()->subDays(30), now()]
                    )
            );
    }
}
```

**Send re-engagement email when they enter this segment:**

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectEnteredSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReEngagementOffer;

class SendReEngagementEmail implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {
        if ($event->segment->as_class === 'AtRiskCustomers') {
            $user = $event->object;

            Mail::to($user)->send(new ReEngagementOffer($user));
        }
    }
}
```

---

## Find Power Users

Identify your most engaged customers:

```php
<?php

namespace App\Segments;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Contracts\Segment;
use StickleApp\Core\Filters\Filter;

class PowerUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            // High session count
            ->stickleWhere(
                Filter::sessionCount()
                    ->count()
                    ->betweenDates(
                        startDate: now()->subDays(30),
                        endDate: now()
                    )
                    ->greaterThan(20)
            )
            // High feature usage
            ->stickleWhere(
                Filter::eventCount('feature:used')
                    ->count()
                    ->betweenDates(
                        startDate: now()->subDays(30),
                        endDate: now()
                    )
                    ->greaterThan(50)
            )
            // Growing engagement
            ->stickleWhere(
                Filter::eventCount('page_view')
                    ->count()
                    ->increased()
                    ->betweenDateRanges(
                        compareToDateRange: [now()->subDays(60), now()->subDays(30)],
                        currentDateRange: [now()->subDays(30), now()]
                    )
            );
    }
}
```

---

## Send Email When User Enters Segment

Automatically notify users when they achieve a milestone:

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectEnteredSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\HighValueWelcome;
use App\Mail\PowerUserBadge;

class SegmentEntryNotification implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {
        $user = $event->object;
        $segment = $event->segment->as_class;

        match ($segment) {
            'HighValueCustomers' => Mail::to($user)->send(new HighValueWelcome()),
            'PowerUsers' => Mail::to($user)->send(new PowerUserBadge()),
            default => null,
        };
    }
}
```

---

## Track Feature Adoption

Monitor which features users are adopting:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use StickleApp\Core\Traits\StickleEntity;

class User extends Model
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'features_used_count',
            'core_features_adopted',
            'advanced_features_adopted',
        ];
    }

    protected function featuresUsedCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->featureUsage()
                ->where('last_used_at', '>=', now()->subDays(30))
                ->distinct('feature_name')
                ->count()
        );
    }

    protected function coreFeaturesAdopted(): Attribute
    {
        $coreFeatures = ['dashboard', 'reports', 'export'];

        return Attribute::make(
            get: fn () => $this->featureUsage()
                ->whereIn('feature_name', $coreFeatures)
                ->where('last_used_at', '>=', now()->subDays(30))
                ->distinct('feature_name')
                ->count()
        );
    }

    protected function advancedFeaturesAdopted(): Attribute
    {
        $advancedFeatures = ['api', 'webhooks', 'integrations'];

        return Attribute::make(
            get: fn () => $this->featureUsage()
                ->whereIn('feature_name', $advancedFeatures)
                ->where('last_used_at', '>=', now()->subDays(30))
                ->distinct('feature_name')
                ->count()
        );
    }
}
```

**Track feature usage from JavaScript:**

```javascript
// When user uses a feature
stickle.track('feature:used', {
    feature_name: 'export',
    format: 'csv',
    records: 1500
});
```

---

## Calculate Customer Health Score

Build a comprehensive health score:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use StickleApp\Core\Traits\StickleEntity;

class Customer extends Model
{
    use StickleEntity;

    public static function stickleObservedAttributes(): array
    {
        return [
            'health_score', // Alert when score changes
        ];
    }

    public static function stickleTrackedAttributes(): array
    {
        return [
            'health_score',
            'engagement_score',
            'payment_score',
            'support_score',
        ];
    }

    protected function healthScore(): Attribute
    {
        return Attribute::make(
            get: function () {
                $score = 0;

                // Engagement (40 points max)
                $score += $this->engagementScore * 0.4;

                // Payment health (30 points max)
                $score += $this->paymentScore * 0.3;

                // Support interactions (20 points max)
                $score += $this->supportScore * 0.2;

                // Feature adoption (10 points max)
                $featuresUsed = $this->features_used_count ?? 0;
                $score += min(10, $featuresUsed * 2);

                return round($score);
            }
        );
    }

    protected function engagementScore(): Attribute
    {
        return Attribute::make(
            get: function () {
                $score = 100;

                $daysSinceLogin = $this->last_login_at?->diffInDays(now()) ?? 999;

                if ($daysSinceLogin > 30) $score -= 60;
                elseif ($daysSinceLogin > 14) $score -= 40;
                elseif ($daysSinceLogin > 7) $score -= 20;

                $monthlyActiveDays = $this->monthly_active_days ?? 0;
                if ($monthlyActiveDays < 5) $score -= 20;

                return max(0, $score);
            }
        );
    }

    protected function paymentScore(): Attribute
    {
        return Attribute::make(
            get: function () {
                $score = 100;

                if ($this->subscription_status === 'past_due') $score -= 50;
                if ($this->subscription_status === 'canceled') return 0;
                if ($this->failed_payments_count > 0) $score -= 20;

                return max(0, $score);
            }
        );
    }

    protected function supportScore(): Attribute
    {
        return Attribute::make(
            get: function () {
                $score = 100;

                $openTickets = $this->supportTickets()
                    ->where('status', 'open')
                    ->count();

                $score -= ($openTickets * 10);

                return max(0, $score);
            }
        );
    }
}
```

**Alert team when health score drops:**

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectAttributeChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LowHealthScoreAlert;

class CustomerHealthScoreListener implements ShouldQueue
{
    public function handle(ObjectAttributeChanged $event): void
    {
        $customer = $event->object;
        $newScore = $event->newValue;
        $oldScore = $event->oldValue;

        // Alert if score drops below 50
        if ($newScore < 50 && $oldScore >= 50) {
            Notification::route('slack', config('slack.cs_webhook'))
                ->notify(new LowHealthScoreAlert($customer, $newScore));
        }

        // Escalate if critical
        if ($newScore < 25) {
            // Notify account manager directly
            $customer->accountManager->notify(new LowHealthScoreAlert($customer, $newScore));
        }
    }
}
```

---

## Slack Notification on High-Value Login

Alert your team when VIP customers log in:

```php
<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VIPLogin;
use StickleApp\Core\Filters\Filter;

class NotifyVIPLogin implements ShouldQueue
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Check if high-value customer
        $isHighValue = $user->newQuery()
            ->where('id', $user->id)
            ->stickleWhere(
                Filter::segment('HighValueCustomers')->isInSegment()
            )
            ->exists();

        if ($isHighValue) {
            Notification::route('slack', config('slack.vip_webhook'))
                ->notify(new VIPLogin($user));
        }
    }
}
```

---

## Update CRM When MRR Changes

Sync customer data to your CRM:

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectAttributeChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\CRMService;

class AccountMrrListener implements ShouldQueue
{
    public function __construct(
        protected CRMService $crm
    ) {}

    public function handle(ObjectAttributeChanged $event): void
    {
        $account = $event->object;
        $newMRR = $event->newValue;
        $oldMRR = $event->oldValue;

        // Update CRM
        $this->crm->updateAccount($account->id, [
            'mrr' => $newMRR,
            'mrr_change' => $newMRR - $oldMRR,
            'updated_at' => now(),
        ]);

        // Tag high-value accounts
        if ($newMRR >= 5000 && $oldMRR < 5000) {
            $this->crm->addTag($account->id, 'high-value');
        }
    }
}
```

---

## Trial Conversion Tracking

Track trial users and conversion:

```php
<?php

namespace App\Segments;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Contracts\Segment;
use StickleApp\Core\Filters\Filter;

class ActiveTrialUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->where('subscription_status', 'trial')
            ->where('trial_ends_at', '>', now())
            ->stickleWhere(
                Filter::sessionCount()
                    ->count()
                    ->betweenDates(now()->subDays(7), now())
                    ->greaterThan(3)
            );
    }
}

class TrialExpiringS Soon extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->where('subscription_status', 'trial')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(3)]);
    }
}
```

**Send conversion email before trial expires:**

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectEnteredSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\TrialExpiringEmail;

class SendTrialExpiringEmail implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {
        if ($event->segment->as_class === 'TrialExpiringSoon') {
            $user = $event->object;

            Mail::to($user)->send(new TrialExpiringEmail($user));
        }
    }
}
```

---

## Next Steps

- **[Filters Guide](/guide/filters)** - Master filtering for segments
- **[Event Listeners](/guide/event-listeners)** - Build event-driven workflows
- **[Customer Segments](/guide/segments)** - Create powerful segments
- **[Tracking Attributes](/guide/tracking-attributes)** - Define custom attributes
