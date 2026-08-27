<?php

declare(strict_types=1);

namespace StickleApp\Core\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;

/**
 * A belongsToMany whose pivot stores the related key as text.
 *
 * Stickle's pivots key on `object_uid`, a text column, because an object
 * identifier is not necessarily an integer. A model's key usually is one, and
 * Postgres refuses `bigint = text` rather than coercing, so the join needs an
 * explicit cast that belongsToMany() has no way to express.
 *
 * The alternative was to let the parent build its join and then edit it out of
 * $query->joins afterwards. That worked, but it depended on the shape of the
 * builder's internal join array: a change there produces wrong SQL silently,
 * because array_filter simply matches nothing. Overriding the one method that
 * owns the join means the same change produces a loud failure in a single
 * named place instead.
 *
 * @extends BelongsToMany<Model, Model, Pivot>
 */
class TextKeyBelongsToMany extends BelongsToMany
{
    /**
     * Join the pivot on the related key cast to text.
     *
     * @param  Builder<Model>|null  $query
     * @return $this
     */
    protected function performJoin($query = null)
    {
        $query = $query ?: $this->query;

        $query->join(
            $this->table,
            DB::raw($this->getQualifiedRelatedKeyName().'::text'),
            '=',
            $this->getQualifiedRelatedPivotKeyName()
        );

        return $this;
    }
}
