<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserPermissionRequest extends FormRequest
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
        $rules = [
            'subject_type' => 'sometimes|required|string',
            'subject_id' => ['sometimes', 'required', 'integer'],
            'permission_type' => 'sometimes|required|string|in:broker,country,zone,seo,translator',
            'resource_id' => 'nullable|integer',
            'resource_value' => 'nullable|string|max:255',
            'action' => 'sometimes|required|string|in:view,edit,delete,manage',
            'metadata' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ];
        
        // Add conditional validation for subject_id based on subject_type
        if ($this->has('subject_type')) {
            $subjectType = $this->input('subject_type');
            
            if (in_array($subjectType, ['PlatformUser', 'platform_user'])) {
                $rules['subject_id'][] = Rule::exists('platform_users', 'id');
            } elseif (in_array($subjectType, ['BrokerTeamUser', 'broker_team_user'])) {
                $rules['subject_id'][] = Rule::exists('broker_team_users', 'id');
            }
        }
        
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
            'subject_type' => 'subject type',
            'subject_id' => 'subject ID',
            'permission_type' => 'permission type',
            'resource_id' => 'resource ID',
            'resource_value' => 'resource value',
            'action' => 'action',
            'metadata' => 'metadata',
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
            'subject_type.required' => 'The subject type is required',
            'subject_id.required' => 'The subject ID is required',
            'subject_id.exists' => 'The selected subject does not exist',
            'permission_type.required' => 'The permission type is required',
            'permission_type.in' => 'The permission type must be one of: broker, country, zone, seo, translator',
            'action.required' => 'The action is required',
            'action.in' => 'The action must be one of: view, edit, delete, manage',
        ];
    }
}

