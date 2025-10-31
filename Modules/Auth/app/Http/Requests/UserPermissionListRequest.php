<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPermissionListRequest extends FormRequest
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
            'order_by' => 'nullable|string|in:id,subject_type,subject_id,permission_type,resource_id,resource_value,action,is_active,created_at,updated_at',
            'order_direction' => 'nullable|string|in:asc,desc',
            'subject_type' => 'nullable|string',
            'subject_id' => 'nullable|integer',
            'permission_type' => 'nullable|string|in:broker,country,zone,seo,translator',
            'resource_id' => 'nullable|integer',
            'resource_value' => 'nullable|string|max:255',
            'action' => 'nullable|string|in:view,edit,delete,manage',
            'subject' => 'nullable|string|max:255',
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
            'subject_type' => 'subject type',
            'subject_id' => 'subject ID',
            'permission_type' => 'permission type',
            'resource_id' => 'resource ID',
            'resource_value' => 'resource value',
            'action' => 'action',
            'subject' => 'subject search',
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
            'order_by.in' => 'Invalid sort column. Allowed columns: id, subject_type, subject_id, permission_type, resource_id, resource_value, action, is_active, created_at, updated_at',
            'permission_type.in' => 'Invalid permission type. Allowed types: broker, country, zone, seo, translator',
            'action.in' => 'Invalid action. Allowed actions: view, edit, delete, manage',
            'is_active.boolean' => 'The active status must be true or false',
        ];
    }
}

