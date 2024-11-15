<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Http\Requests;

use Dclaysmith\LaravelCascade\Enums\RequestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IngestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|string>>
     */
    public function rules(): array
    {
        return [
            'payload' => ['required', 'array'],
            'payload.*.model' => ['sometimes', $this->availableModels()],
            'payload.*.type' => ['required', Rule::enum(RequestType::class)],
            'payload.*.uid' => ['required', 'string', 'alpha_dash:ascii'],
            'payload.*.name' => ['required_if:type,track', 'string', 'alpha_dash:ascii'],
            'payload.*.url' => ['required_if:type,page', 'string', 'url'],
            'payload.*.data' => ['sometimes_if:type,track', 'array'],
            'payload.*.timestamp' => ['sometims', 'nullable', 'date'],
        ];
    }

    private function availableModels(): array
    {
        $config = config('cascade.models', []);

        return array_filter($config, fn ($value) => ! is_null($value));
    }
}
