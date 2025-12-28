<?php

namespace Modules\Auth\Http\Requests;

use App\Http\Requests\BaseRequest;
use Modules\Auth\Tables\UserPermissionsTableConfig;

class UserPermissionListRequest extends BaseRequest
{
    protected function tableConfigClass(): string
    {
        return UserPermissionsTableConfig::class;
    }
    protected function formConfigClass(): ?string
    {
        return null;
    }
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
        $tableConfig = $this->getTableConfig();
        $filtersConstraints = $tableConfig?->getFiltersConstraints() ?? [];
        $sortableColumns = $tableConfig?->getSortableColumns() ?? [];
        $rules = [
            ...$filtersConstraints,
            'order_by' => 'nullable|string|in:'.implode(',', array_keys($sortableColumns)),
            'order_direction' => 'nullable|string|in:asc,desc,ASC,DESC',
            'per_page' => 'nullable|integer|min:1|max:10000000',
        ];
        return $rules;
       
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

