<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAffiliateLinkRequest extends FormRequest
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
            'url_type' => 'sometimes|string|max:100',
            'account_type_id' => 'sometimes|nullable|integer|exists:account_types,id',
            'name' => 'sometimes|string|max:500',
            'url' => 'sometimes|url',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
            'is_master_link' => 'sometimes|nullable|boolean',
            'platform_urls' => 'sometimes|array',
            'platform_urls.*.id' => 'required_with:platform_urls|integer|exists:urls,id',
            'platform_urls.*.name' => 'required_with:platform_urls|string|max:500',
            'currency' => 'required|string|max:10',
        ];
    }
}
