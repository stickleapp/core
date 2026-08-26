<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use StickleApp\Core\Dto\ModelDto;
use StickleApp\Core\Dto\RequestDto;
use StickleApp\Core\Enums\RequestType;
use StickleApp\Core\Events\Page;
use StickleApp\Core\Events\Track;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

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
            'payload.*.model_class' => ['sometimes', Rule::in($this->availableModels())],
            'payload.*.object_uid' => ['sometimes', 'string', 'alpha_dash:ascii'],
            /**
             * The properties bag needs a rule of its own. validated() returns
             * only keys a rule covers, so with just the properties.name rule
             * below every other client-supplied property -- the page url and
             * title among them -- was silently dropped before it was stored.
             */
            'payload.*.properties' => ['sometimes', 'array'],
            'payload.*.properties.name' => ['required_if:type,track', 'string', 'alpha_dash:ascii'],
            'payload.*.timestamp' => ['sometimes', 'nullable', 'date'],
        ];

        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            Log::debug('Request failed', [
                'request' => $request->getContent(),
            ]);

            throw new Exception('Request invalid');
        }

        $validated = $validator->validated();

        $dt = new Carbon;

        /**
         * These describe the page the event happened on, not the beacon POST
         * that carried it. Defaulting url and path from the request recorded
         * every row at /stickle/api/track with method POST -- and path is one
         * of the eight columns in the rollup unique index, so the constant was
         * baked into every aggregated row.
         *
         * The client sends url for a page view. For anything else the Referer
         * header is the page the beacon fired from, which is the same answer.
         */
        $defaultProperties = [
            'title' => $request->header('X-Title', ''),
            'url' => $request->headers->get('referer') ?: null,
            'referrer' => '',
            'user_agent' => $request->userAgent(),
        ];

        /**
         * The route is registered in the api middleware group, which starts no
         * session, so $request->session() raises rather than returning null.
         * A missing session is not an error here -- session_uid is nullable and
         * the visitor may genuinely not have one. Hosts that want the beacon's
         * events scoped to a session can add the session middleware through
         * stickle.routes.api.middleware, and this picks it up.
         */
        $sessionUid = $request->hasSession() ? $request->session()->getId() : null;

        foreach (data_get($validated, 'payload') as $index => $item) {

            throw_unless($modelClass = $this->modelClass(
                data_get($item, 'model_class'),
                $request->user()
            ), Exception::class, 'Model class not specified');

            throw_unless($objectUid = $this->objectUid(
                data_get($item, 'object_uid'),
                $request->user()
            ), Exception::class, 'Object id not specified');

            /**
             * Read from the raw payload, not from $validated: validated()
             * returns only what a rule covers, and the properties.name rule
             * narrows the bag to that one key -- so everything else the client
             * sent, the page url included, was dropped here before it was
             * stored. name itself stays validated above, since TrackListener
             * turns it into a class name.
             */
            $itemProperties = $this->withDerivedPath(array_merge(
                $defaultProperties,
                (array) data_get($payload, "payload.{$index}.properties", [])
            ));

            $requestDto = new RequestDto(
                type: $item['type'] === 'track' ? 'event' : 'request',
                model_class: $modelClass,
                object_uid: $objectUid,
                session_uid: $sessionUid,
                timestamp: data_get($item, 'timestamp', $dt),
                model: $this->getModelDto($modelClass, $objectUid),
                ip_address: $request->ip(),
                properties: $itemProperties,
                location_data: null
            );

            switch ($item['type']) {
                case 'page':
                    event(new Page($requestDto));
                    break;
                case 'track':
                    event(new Track($requestDto));
                    break;
            }
        }

        return response()->noContent();
    }

    /**
     * Fill in path and search from the reported url, so they describe the page
     * rather than the beacon. An explicitly supplied path is left alone.
     *
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    private function withDerivedPath(array $properties): array
    {
        $url = $properties['url'] ?? null;

        if (! is_string($url) || $url === '') {
            return $properties;
        }

        $properties['path'] ??= parse_url($url, PHP_URL_PATH) ?: '/';
        $properties['search'] ??= parse_url($url, PHP_URL_QUERY) ?: null;

        return $properties;
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
            return $explicit;
        }

        if ($model && property_exists($model, 'id')) {
            return (string) $model->id;
        }

        return null;
    }

    /**
     * The trackable models, in the shape model_class is stored and sent in:
     * a basename. getClassesWithTrait() returns fully-qualified names, so
     * comparing them to an incoming value directly would never match.
     *
     * @return array<string>
     */
    private function availableModels(): array
    {
        return array_map(
            ClassUtils::storeModelClass(...),
            ClassUtils::getClassesWithTrait(
                config('stickle.namespaces.models'),
                StickleEntity::class
            )
        );
    }

    private function getModelDto(string $modelClass, string $objectUid): ModelDto
    {
        $fullModelClass = ClassUtils::resolveModelClass($modelClass);

        throw_unless(ClassUtils::usesTrait($fullModelClass, StickleEntity::class), Exception::class, 'Model does not use StickleTrait.');

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
