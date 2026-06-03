<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexOptionValueRequest extends FormRequest
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
            'entity_type' => 'required|string|max:255',
            'entity_id' => 'sometimes|integer|exists:option_values,optionable_id',
            'broker_option_id' => 'sometimes|integer|exists:broker_options,id',
            'category_id' => 'sometimes|integer|exists:option_categories,id',
            'option_slug' => 'sometimes|string|exists:broker_options,option_slug',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1',
            'language_code' => 'nullable|string|max:255',
            'zone_code' => 'nullable|string|max:255',
            'sort_by' => 'nullable|string|in:option_slug,value,status,created_at,updated_at',
            'search' => 'nullable|string|max:255',
            'visibile_for' => 'nullable|string|in:all,broker,admin',
            'sort_direction' => 'nullable|string|in:asc,desc',
        ];
    }
}
