<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationStepIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'language_code' => $this->input('language_code') ?? 'en',
        ]);
    }

    public function rules(): array
    {
        return [
            'language_code' => 'sometimes|string|max:10',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
        ];
    }
}

