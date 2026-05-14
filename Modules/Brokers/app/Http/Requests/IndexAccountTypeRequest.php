<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexAccountTypeRequest extends FormRequest
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
            'zone_code' => 'sometimes|string|exists:zones,code',
            'sort_by' => 'sometimes|string|in:id,created_at,updated_at',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'per_page' => 'sometimes|integer',
            'language_code' => 'sometimes|string',
            'account_type_id' => 'sometimes|integer|exists:account_types,id',
            'page' => 'sometimes|integer|min:1',
        ];
    }

   

    public function attributes(): array
    {
        return [
            'zone_code' => 'zone code',
            'sort_by' => 'sort by',
            'sort_direction' => 'sort direction',
        ];
    }

    public function messages(): array
    {
        return [
            'zone_code.exists' => 'Zone not found',
            'sort_by.in' => 'Invalid sort by',
            'sort_direction.in' => 'Invalid sort direction',
        ];
    }
}
