<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetRegulatorsListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'language_code' => $this->input('language_code') ?? 'en',
            'zone_id' => $this->input('zone_id'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'language_code' => ['required', 'string', 'max:10'],
            'zone_id' => ['sometimes', 'nullable', 'integer', 'exists:zones,id'],
        ];
    }
}
