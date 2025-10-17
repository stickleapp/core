<?php

namespace StickleApp\Core\Http\Controllers\Requests;

use Override;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RequestsIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'model_class' => ['nullable', 'string'],
            'object_uid' => ['nullable', 'string'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'event_types' => ['nullable', 'string', 'in:page_view,event'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:250'],
            'include_location' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    #[Override]
    public function attributes(): array
    {
        return [
            'model_class' => 'model class',
            'object_uid' => 'object UID',
            'start_at' => 'start date',
            'end_at' => 'end date',
            'event_types' => 'event types',
            'include_location' => 'include location',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'include_location' => $this->boolean('include_location', true),
            'limit' => $this->integer('limit', 250),
            'start_at' => $this->input('start_at', now()->subMinutes(30)->toDateTimeString()),
        ]);
    }
}
