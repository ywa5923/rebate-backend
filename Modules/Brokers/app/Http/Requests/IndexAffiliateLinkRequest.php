<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexAffiliateLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'language_code' => $this->input('language_code') ?? 'en',
            'zone_code' => $this->input('zone_code') ?? null,
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'language_code' => 'required|string|max:10',
            'zone_code' => 'nullable|string|max:100|exists:zones,zone_code',
        ];
    }
}
