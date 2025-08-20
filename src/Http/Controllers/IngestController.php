<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use StickleApp\Core\Dto\ModelDto;
use StickleApp\Core\Dto\RequestDto;
use StickleApp\Core\Enums\RequestType;
use StickleApp\Core\Events\Page;
use StickleApp\Core\Events\Track;
use StickleApp\Core\Support\ClassUtils;

class IngestController
{
    /**
     * Store a newly created track in storage.
     */
    public function store(Request $request): Response
    {

        Log::debug('IngestController', [
            $request->getContent(),
        ]);

        $payload = json_decode($request->getContent(), true);

        $rules = [
            'payload' => ['required', 'array'],
            'payload.*.type' => ['required', Rule::enum(RequestType::class)],
            'payload.*.model_class' => ['sometimes', $this->availableModels()],
            'payload.*.object_uid' => ['sometimes', 'string', 'alpha_dash:ascii'],
            'payload.*.properties.name' => ['required_if:type,track', 'string', 'alpha_dash:ascii'],
            'payload.*.timestamp' => ['sometimes', 'nullable', 'date'],
        ];

        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            Log::debug('Request failed', [
                'request' => $request->getContent(),
            ]);

            throw new \Exception('Request invalid');
        }

        $validated = $validator->validated();

        $dt = new Carbon;

        $defaultProperties = [
            'title' => $request->header('X-Title', ''),
            'path' => $request->getPathInfo(),
            'url' => $request->fullUrl(),
            'referrer' => $request->headers->get('referer', ''),
            'search' => $request->getQueryString(),
            'user_agent' => $request->userAgent(),
            'method' => $request->getMethod(),
        ];

        foreach (data_get($validated, 'payload') as $item) {

            if (! $modelClass = $this->modelClass(
                data_get($item, 'model_class'),
                $request->user()
            )) {
                throw new \Exception('Model class not specified');
            }

            if (! $objectUid = $this->objectUid(
                data_get($item, 'object_uid'),
                $request->user()
            )) {
                throw new \Exception('Object id not specified');
            }

            $itemProperties = array_merge($defaultProperties, data_get($item, 'properties', []));

            $requestDto = new RequestDto(
                type: $item['type'] === 'track' ? 'event' : $item['type'],
                model_class: $modelClass,
                object_uid: $objectUid,
                session_uid: $request->session()->getId(),
                ip_address: $request->ip(),
                properties: $itemProperties,
                timestamp: data_get($item, 'timestamp', $dt),
                location_data: null,
                model: $this->getModelDto($modelClass, $objectUid)
            );

            switch ($item['type']) {
                case 'page':
                    Page::dispatch($requestDto);
                    break;
                case 'track':
                    Track::dispatch($requestDto);
                    break;
            }
        }

        return response()->noContent();
    }

    private function modelClass(?string $explicit, ?object $object): ?string
    {
        if ($explicit) {
            return $explicit;
        }

        if ($object) {
            return class_basename($object);
        }

        return null;
    }

    private function objectUid(?string $explicit, ?object $model): ?string
    {
        if ($explicit) {
            return (string) $explicit;
        }

        if ($model && property_exists($model, 'id')) {
            return (string) $model->id;
        }

        return null;
    }

    /**
     * @return array<string>
     */
    private function availableModels(): array
    {
        return ClassUtils::getClassesWithTrait(
            config('stickle.namespaces.models'),
            \StickleApp\Core\Traits\StickleEntity::class
        );
    }

    private function getModelDto(string $modelClass, string $objectUid): ModelDto
    {
        $fullModelClass = config('stickle.namespaces.models').'\\'.Str::ucfirst($modelClass);

        if (! class_exists($fullModelClass)) {
            throw new \Exception('Model not found: '.$fullModelClass);
        }

        if (! ClassUtils::usesTrait($fullModelClass, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            throw new \Exception('Model does not use StickleTrait.');
        }

        $model = $fullModelClass::findOrFail($objectUid);

        return new ModelDto(
            model_class: $fullModelClass,
            object_uid: $objectUid,
            label: $model->stickleLabel(),
            raw: $model->toArray(),
            url: $model->stickleUrl()
        );
    }
}
