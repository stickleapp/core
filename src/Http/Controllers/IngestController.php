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

        if (! $modelClass = $this->modelClass(
            data_get($validated, 'model_class'),
            $request->user()
        )) {
            throw new \Exception('Model class not specified');
        }

        if (! $objectUid = $this->objectUid(
            data_get($validated, 'object_uid'),
            $request->user()
        )) {
            throw new \Exception('Object id not specified');
        }

        $properties = data_get($validated, 'payload.*.properties', []);

        $properties['title'] = data_get($properties, 'title', $request->header('X-Title', ''));
        $properties['path'] = data_get($properties, 'path', $request->getPathInfo());
        $properties['url'] = data_get($properties, 'url', $request->fullUrl());
        $properties['referrer'] = data_get($properties, 'referrer', $request->headers->get('referer', ''));
        $properties['search'] = data_get($properties, 'search', $request->getQueryString());
        $properties['user_agent'] = data_get($properties, 'user_agent', $request->userAgent());
        $properties['method'] = data_get($properties, 'method', $request->getMethod());

        foreach (data_get($validated, 'payload') as $item) {
            switch ($item['type']) {
                case 'page':
                    $data = array_merge($item, [
                        'activity_type' => 'page',
                        'model_class' => $modelClass,
                        'object_uid' => $objectUid,
                        'session_uid' => $request->session()->getId(),
                        'ip_address' => $request->ip(),
                        'model' => $this->getModel($modelClass, $objectUid),
                        'name' => data_get($item, 'name'),
                        'properties' => $properties,
                        'timestamp' => data_get($item, 'timestamp', $dt),
                    ]);

                    Page::dispatch($data);
                    break;
                case 'track':
                    $data = array_merge($item, [
                        'activity_type' => 'event',
                        'model_class' => $modelClass,
                        'object_uid' => $objectUid,
                        'session_uid' => $request->session()->getId(),
                        'ip_address' => $request->ip(),
                        'model' => $this->getModel($modelClass, $objectUid),
                        'name' => data_get($item, 'name'),
                        'properties' => $properties,
                        'timestamp' => data_get($item, 'timestamp', $dt),
                    ]);

                    Track::dispatch($data);
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
        dd(class_basename($object));
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

    /**
     * @return array<string, mixed>
     */
    private function getModel(string $modelClass, string $objectUid): array
    {

        $modelClass = config('stickle.namespaces.models').'\\'.Str::ucfirst($modelClass);

        if (! class_exists($modelClass)) {
            throw new \Exception('Model not found: '.$modelClass);
        }

        if (! ClassUtils::usesTrait($modelClass, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            throw new \Exception('Model does not use StickleTrait.');
        }

        $model = $modelClass::findOrFail($objectUid);

        return [
            'class' => $modelClass,
            'uid' => $objectUid,
            'label' => $model->stickleLabel(),
            'url' => $model->stickleUrl(),
        ];
    }
}
