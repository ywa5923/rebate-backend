<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffiliateLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // $this->merge([
        //     'language_code' => $this->input('language_code') ?? 'en',
        // ]);
    }

    public function rules(): array
    {
        return [
            'url_type' => 'required|string|max:100',
            'account_type_id' => 'sometimes|nullable|integer|exists:account_types,id',
            'name' => 'required|string|max:500',
            'url' => 'required|url',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
            'is_master_link' => 'sometimes|nullable|boolean',
        ];
    }
}
