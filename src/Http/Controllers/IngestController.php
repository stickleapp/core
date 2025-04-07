<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use StickleApp\Core\Enums\RequestType;
use StickleApp\Core\Events\Page;
use StickleApp\Core\Events\Track;

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
            'payload.*.model' => ['sometimes', $this->availableModels()],
            'payload.*.object_uid' => ['sometimes', 'string', 'alpha_dash:ascii'],
            'payload.*.name' => ['required_if:type,track', 'string', 'alpha_dash:ascii'],
            'payload.*.url' => ['required_if:type,page', 'string', 'url'],
            'payload.*.data' => ['sometimes_if:type,track', 'array'],
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

        if (! $model = $this->model(
            data_get($validated, 'model'),
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
        foreach (data_get($validated, 'payload') as $item) {
            switch ($item['type']) {
                case 'page':
                    $data = array_merge($item, [
                        'user' => $request->user(),
                        'model' => $model,
                        'object_uid' => $objectUid,
                        'session_uid' => $request->session()->getId(),
                        'url' => $request->fullUrl(),
                        'path' => $request->getPathInfo(),
                        'host' => $request->getHost(),
                        'search' => $request->getQueryString(),
                        'utm_source' => $request->query('utm_source'),
                        'utm_medium' => $request->query('utm_medium'),
                        'utm_campaign' => $request->query('utm_campaign'),
                        'utm_content' => $request->query('utm_content'),
                        'created_at' => data_get($item, 'timestamp', $dt),
                        'updated_at' => data_get($item, 'timestamp', $dt),
                    ]);

                    Page::dispatch($data);
                    break;
                case 'track':
                    $data = array_merge($item, [
                        'user' => $request->user(),
                        'model' => $model,
                        'object_uid' => $objectUid,
                        'session_uid' => $request->session()->getId(),
                        'url' => $request->fullUrl(),
                        'path' => $request->getPathInfo(),
                        'host' => $request->getHost(),
                        'search' => $request->getQueryString(),
                        'utm_source' => $request->query('utm_source'),
                        'utm_medium' => $request->query('utm_medium'),
                        'utm_campaign' => $request->query('utm_campaign'),
                        'utm_content' => $request->query('utm_content'),
                        'created_at' => data_get($item, 'timestamp', $dt),
                        'updated_at' => data_get($item, 'timestamp', $dt),
                    ]);

                    Track::dispatch($data);
                    break;
            }
        }

        return response()->noContent();
    }

    private function model(?string $explicit, ?object $object): ?string
    {
        if ($explicit) {
            return $explicit;
        }

        if ($object) {
            return get_class($object);
        }

        return null;
    }

    private function objectUid(?string $explicit, ?object $object): ?string
    {
        if ($explicit) {
            return $explicit;
        }

        if ($object && property_exists($object, 'id')) {
            return $model->id;
        }

        return null;
    }

    /**
     * @return array<string>
     */
    private function availableModels(): array
    {
        $config = config('stickle.models', []);

        return array_filter($config, fn ($value) => ! is_null($value));
    }
}
