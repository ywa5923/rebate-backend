<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexEvaluationStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'language_code' => $this->input('language_code') ?? 'en',
            'zone_id' => $this->input('zone_id') ?? null,
            'broker_id' => $this->route('broker_id'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'language_code' => 'required|string|max:10',
            'zone_id' => 'nullable|integer|exists:zones,id',
            'broker_id' => 'required|integer|exists:brokers,id',
        ];
    }
}
