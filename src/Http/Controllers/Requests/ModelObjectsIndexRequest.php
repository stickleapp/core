<?php

namespace StickleApp\Core\Http\Controllers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModelObjectsIndexRequest extends FormRequest
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
            'model' => ['required', 'string'],
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer'],
        ];
    }
}
