<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZoneListRequest extends FormRequest
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
            'per_page' => 'nullable|integer|min:1|max:100',
            'order_by' => 'nullable|string|in:id,name,zone_code,created_at,updated_at',
            'order_direction' => 'nullable|string|in:asc,desc',
            'name' => 'nullable|string|max:255',
            'zone_code' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'per_page' => 'items per page',
            'order_by' => 'sort column',
            'order_direction' => 'sort direction',
            'zone_code' => 'zone code',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.max' => 'You cannot request more than 100 items per page',
            'order_by.in' => 'Invalid sort column. Allowed columns: id, name, zone_code, created_at, updated_at',
        ];
    }
}

