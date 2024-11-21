<?php

namespace Dclaysmith\LaravelCascade\Http\Controllers;

use Carbon\Carbon;
use Dclaysmith\LaravelCascade\Events\Page;
use Dclaysmith\LaravelCascade\Events\Track;
use Dclaysmith\LaravelCascade\Http\Requests\IngestRequest;

class IngestController
{
    /**
     * Store a newly created track in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(IngestRequest $request)
    {

        $payload = $request->collect('payload');

        $dt = new Carbon;

        if (! $model = $this->model(
            data_get($payload, 'model'),
            $request->user()
        )) {
            return response()->json([
                'message' => 'Model not found',
            ], 404);
        }

        if (! $objectUid = $this->objectUid(
            data_get($payload, 'uid'),
            $request->user()
        )) {
            return response()->json([
                'message' => 'Uid not found',
            ], 404);
        }

        switch ($payload['type']) {
            case 'page':
                $data = array_merge($payload, [
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
                    'created_at' => data_get($payload, 'timestamp', $dt),
                    'created_at' => data_get($payload, 'timestamp', $dt),
                ]);

                Page::dispatch($data);

                return response()->noContent();
            case 'track':
                $data = array_merge($payload, [
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
                    'created_at' => data_get($payload, 'timestamp', $dt),
                    'created_at' => data_get($payload, 'timestamp', $dt),
                ]);

                Track::dispatch($data);

                return response()->noContent();
            default:
                return response()->json([
                    'message' => 'Type not found',
                ], 404);
        }
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
}
