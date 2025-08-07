<?php

namespace StickleApp\Core\Http\Controllers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivitiesIndexRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'model_class' => ['required', 'string'],
            'object_uid' => ['sometimes', 'string'],
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['sometimes', 'date', 'after_or_equal:start_at'],
            'event_types' => ['sometimes', 'string', 'in:page_view,event'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'include_location' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
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
            'limit' => $this->integer('limit', 50),
            'start_at' => $this->input('start_at', now()->subMinutes(30)->toDateTimeString()),
            'end_at' => $this->input('end_at', now()->toDateTimeString()),
        ]);
    }
}
