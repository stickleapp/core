<?php

namespace StickleApp\Core\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use StickleApp\Core\Models\Request as RequestModel;
use StickleApp\Core\Support\ClassUtils;

/**
 * @mixin RequestModel
 */
class RequestResource extends JsonResource
{
    public function __construct(RequestModel $request)
    {
        parent::__construct($request);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'activity_type' => $this->activity_type,
            'model_class' => $this->model_class,
            'object_uid' => $this->object_uid,
            'session_uid' => $this->session_uid,
            'location' => $this->getLocationData(),
            'ip_address' => $this->ip_address,
            'properties' => $this->when(! empty($this->properties), $this->properties),
            'model' => $this->getModel(),
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getLocationData(): ?array
    {
        if (! $this->relationLoaded('locationData') || ! $this->locationData) {
            return null;
        }

        $locationData = $this->locationData;
        $coordinates = null;

        if (isset($locationData['coordinates']) && $locationData['coordinates']) {
            // Extract lat/lng from PostGIS point
            $point = $locationData['coordinates'];
            $coordinates = [
                'lat' => $point->getLat(),
                'lng' => $point->getLng(),
            ];
        }

        return [
            'city' => $locationData['city'] ?? null,
            'country' => $locationData['country'] ?? null,
            'coordinates' => $coordinates,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getModel(): array
    {

        $modelClass = config('stickle.namespaces.models').'\\'.Str::ucfirst($this->model_class);

        if (! class_exists($modelClass)) {
            throw new \Exception('Model not found: '.$modelClass);
        }

        if (! ClassUtils::usesTrait($modelClass, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            throw new \Exception('Model does not use StickleTrait.');
        }

        $model = $modelClass::findOrFail($this->object_uid);

        return [
            'class' => $this->model_class,
            'uid' => $this->object_uid,
            'label' => $model->stickleLabel(),
            'url' => $model->stickleUrl(),
        ];
    }
}
