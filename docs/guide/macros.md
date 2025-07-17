---
outline: deep
---

NOTE: This is half-baked.

User::stickleFilter(new FilterName()) -- scope
User::stickleSort() -- sort on a ModelAttributs
$user->stickleAttribute()           -- macro (point in time value)
$user->stick

# Scopes

# Macros

`attribute`

```php
namespace App\Models;

use StickleApp\Core\Traits\User as StickleUser;
use Illuminate\Database\Eloquent\Model;

class User extends Model {

    use StickleUser;
}

$user = User::find(1);

$user->stickle()->attribute(attribute: 'user_rating')->value();
```

`history`

```php
namespace App\Models;

use StickleApp\Core\Traits\User as StickleUser;
use Illuminate\Database\Eloquent\Model;

class User extends Model {

    use StickleUser;
}

$user = User::find(1);

$user->stickle()
    ->attribute(attribute: 'user_rating')
    ->history()
    ->since(date: '2024-01-01');
```

```php
namespace App\Models;

use StickleApp\Core\Traits\Group as StickleGroup;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model {

    use StickleGroup;

    public function users(): BelongsToMany
    {
        return $this->hasMany(User::class);
    }
}

$customer = Customer::find(1);

$customer->stickle()->attribute(attribute: 'mrr')->value();
```

```php
$customer = Customer::find(1);

$customer->stickle()
    ->users(withChildGroups: false)
    ->attribute(attribute: 'mrr')
    ->sum();
$customer->stickle()->includeGroups()->includeUsers()->attribute(attribute: 'mrr')->min();
$customer->stickle()->includeGroups()->includeUsers()->attribute(attribute: 'mrr')->max();

$customer->stickle()->includeUsers()->attribute(attribute: 'mrr')->sum();
$customer->stickle()->includeUsers()->attribute(attribute: 'mrr')->min();
$customer->stickle()->includeUsers()->attribute(attribute: 'mrr')->max();
```

```php
class Customer extends Model {

    use StickleGroup;

    public function children(): hasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent(): hasMany
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}

$customer = Customer::find(1);


$customer->stickle()
    ->groups() // ->users()
    ->attribute(attribute: 'mrr')
    ->max()
    ->history()
    ->since(date: '2024-12-11')
    ->get();

```

```json
{
    "mrr": 4,
    "__users": {
        "user_rating": {
            "value": null,
            "min": 1,
            "max": 5,
            "avg": 4.78,
            "count": 23
        }
    },
    "__groups": {
        "mrr": {
            "value": null,
            "min": 188,
            "max": 599,
            "avg": 478,
            "count": 3
        },
        "__users": {
            "user_rating": {
                "value": null,
                "min": 1,
                "max": 5,
                "avg": 4.78,
                "count": 23
            }
        }
    }
}
```
