<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexContestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'zone_code' => $this->input('zone_code') ?? null,
            'language_code' => $this->input('language_code') ?? 'en',
            'broker_id' => $this->route('broker_id'),
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
            'zone_code' => 'sometimes|nullable|string|exists:zones,code',
            'sort_by' => 'sometimes|string|in:id,created_at,updated_at',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'per_page' => 'sometimes|integer',
            'language_code' => 'sometimes|string',
            //'contest_id' => 'sometimes|integer|exists:contests,id',
            'page' => 'sometimes|integer|min:1',
            'broker_id' => 'required|integer|exists:brokers,id',
        ];
    }
}
