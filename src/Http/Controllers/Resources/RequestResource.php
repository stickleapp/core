<?php

namespace StickleApp\Core\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use StickleApp\Core\Support\ClassUtils;

class RequestResource extends JsonResource
{
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
            'location' => $this->when($this->location, json_decode($this->location), null),
            'ip_address' => $this->ip_address,
            'properties' => $this->when($this->properties, json_decode($this->properties), null),
            'model' => $this->getModel(),
            'timestamp' => $this->timestamp,
        ];
    }

    private function getModel()
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
