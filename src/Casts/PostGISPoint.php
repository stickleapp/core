<?php

declare(strict_types=1);

namespace StickleApp\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @implements CastsAttributes<array{lat: float, lng: float}|null, mixed>
 */
class PostGISPoint implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (! $value) {
            return null;
        }

        // Extract coordinates directly from PostGIS point value
        $result = \DB::selectOne(
            'SELECT ST_Y(?::geometry) as lat, ST_X(?::geometry) as lng',
            [$value, $value]
        );

        return $result ? [
            'lat' => (float) $result->lat,
            'lng' => (float) $result->lng,
        ] : null;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value) {
            return null;
        }

        if (is_array($value) && isset($value['lat'], $value['lng'])) {
            // Create PostGIS point using Eloquent's raw expression
            return \DB::raw("ST_GeogFromText('POINT({$value['lng']} {$value['lat']})')");
        }

        return $value;
    }
}
