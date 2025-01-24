---
outline: deep
---

# Creating Custom Scopes

Stickle extends Eloquent so you can query your usage data in a fluent manner. However, it can be repetitive and error prone to repeat these queries. `Local` scopes are a great way to prevent repeating yourself.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Dclaysmith\LaravelCascade\Filters\Targets\RequestCount;

class User extends Model
{
    /**
     * Scope a query to only include users matching the definition of a DAU
     */
    public function scopeActive(Builder $query): void
    {
        $query->where(function ($inner) {
            $inner->stickle(new RequestCount()
                ->occurrences(">", 0)
                ->online()
                ->since(now()->subDays(7));
        });
    }
}
```

Some `stickle` filters can be slow for larger datasets so it may be faster to define an `ActiveUsers` segment and then filter where the `User` is currently in that Segment.

```php
/**
 * Scope a query to only return users currently in the 'ActiveUsers' segment
 */
public function scopeActive(Builder $query): void
{
    $query->where(function ($inner) {
        $inner->stickle(new Segment()
            ->name("ActiveUsers")
            ->in();
    });
}
```

::: warning
Using the `inSegment` filter can return stale results (it is essentially checking a cache). If it is essential that the scope is up-to-date, use the first method.
:::

You can then use the local scope:

```php
use App\Models\User;

$users = User::active()->orderBy('created_at')->get();

```

Read more about local scopes in the [Laravel documentation](https://laravel.com/docs/11.x/eloquent#local-scopes).
