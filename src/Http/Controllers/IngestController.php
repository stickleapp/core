<?php

namespace Dclaysmith\LaravelCascade\Http\Controllers;

use Carbon\Carbon;
use Dclaysmith\LaravelCascade\Enums\RequestType;
use Dclaysmith\LaravelCascade\Events\Page;
use Dclaysmith\LaravelCascade\Events\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

            return response()->json([
                'message' => 'Request failed',
            ], 422);
        }

        $validated = $validator->validated();

        $dt = new Carbon;

        if (! $model = $this->model(
            data_get($validated, 'model'),
            $request->user()
        )) {
            return response()->json([
                'message' => 'Model not found',
            ], 404);
        }

        if (! $objectUid = $this->objectUid(
            data_get($validated, 'object_uid'),
            $request->user()
        )) {
            return response()->json([
                'message' => 'Uid not found',
            ], 404);
        }
        foreach (data_get($validated, 'payload') as $item) {
            switch ($item['type']) {
                case 'page':
                    $data = array_merge($item, [
                        'model' => $model,
                        'object_uid' => $objectUid,
                        'session_uid' => $request->session()?->getId(),
                        'url' => $request->fullUrl(),
                        'path' => $request->getPathInfo(),
                        'host' => $request->getHost(),
                        'search' => $request->getQueryString(),
                        'utm_source' => $request->query('utm_source'),
                        'utm_medium' => $request->query('utm_medium'),
                        'utm_campaign' => $request->query('utm_campaign'),
                        'utm_content' => $request->query('utm_content'),
                        'created_at' => data_get($item, 'timestamp', $dt),
                        'created_at' => data_get($item, 'timestamp', $dt),
                    ]);

                    Page::dispatch($data);
                    break;
                case 'track':
                    $data = array_merge($item, [
                        'model' => $model,
                        'object_uid' => $objectUid,
                        'session_uid' => $request->session()?->getId(),
                        'url' => $request->fullUrl(),
                        'path' => $request->getPathInfo(),
                        'host' => $request->getHost(),
                        'search' => $request->getQueryString(),
                        'utm_source' => $request->query('utm_source'),
                        'utm_medium' => $request->query('utm_medium'),
                        'utm_campaign' => $request->query('utm_campaign'),
                        'utm_content' => $request->query('utm_content'),
                        'created_at' => data_get($item, 'timestamp', $dt),
                        'created_at' => data_get($item, 'timestamp', $dt),
                    ]);

                    Track::dispatch($data);
                    break;
            }
        }

        return response()->noContent();
    }

    private function model($explicit, ?object $object): ?string
    {
        if ($explicit) {
            return $explicit;
        }

        if ($object) {
            return get_class($object);
        }

        return null;
    }

    private function objectUid($explicit, ?object $object): string
    {
        if ($explicit) {
            return $explicit;
        }

        if ($object) {
            return $object->id;
        }

        return null;
    }

    private function availableModels(): array
    {
        $config = config('cascade.models', []);

        return array_filter($config, fn ($value) => ! is_null($value));
    }
}
