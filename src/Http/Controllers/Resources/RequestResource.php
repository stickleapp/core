<?php

namespace StickleApp\Core\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use StickleApp\Core\Dto\ModelDto;
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
            'type' => $this->type,
            'model_class' => $this->model_class,
            'object_uid' => $this->object_uid,
            'session_uid' => $this->session_uid,
            'location' => $this->locationData,
            'ip_address' => $this->ip_address,
            'properties' => $this->when(! empty($this->properties), $this->properties),
            'model' => $this->getModelData(),
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getModelData(): array
    {
        $modelClass = config('stickle.namespaces.models').'\\'.Str::ucfirst($this->model_class);

        if (! class_exists($modelClass)) {
            throw new \Exception('Model not found: '.$modelClass);
        }

        if (! ClassUtils::usesTrait($modelClass, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            throw new \Exception('Model does not use StickleTrait.');
        }

        $model = $modelClass::findOrFail($this->object_uid);

        $modelDto = new ModelDto(
            model_class: $modelClass,
            object_uid: $this->object_uid,
            label: $model->stickleLabel(),
            raw: $model->toArray(),
            url: $model->stickleUrl()
        );

        return $modelDto->toArray();
    }
}
