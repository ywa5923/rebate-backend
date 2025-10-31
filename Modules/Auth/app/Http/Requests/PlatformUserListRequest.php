<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlatformUserListRequest extends FormRequest
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
            'per_page' => 'nullable|integer|min:1',
            'order_by' => 'nullable|string|in:id,name,email,role,is_active,created_at,updated_at',
            'order_direction' => 'nullable|string|in:asc,desc',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
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
            'is_active' => 'active status',
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
            'order_by.in' => 'Invalid sort column. Allowed columns: id, name, email, role, is_active, created_at, updated_at',
            'is_active.boolean' => 'The active status must be true or false',
        ];
    }
}

