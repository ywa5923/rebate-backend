<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformUserRequest extends FormRequest
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
        $userId = $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('platform_users', 'email')->ignore($userId),
                Rule::unique('broker_team_users', 'email'),
            ],
            //'password' => 'nullable|string|min:8',
            'role' => 'sometimes|required|string|in:country_admin,zone_admin,broker_admin,seo_editor,translation_editor',
            'is_active' => 'sometimes|boolean',
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
            'name' => 'name',
            'email' => 'email',
            'password' => 'password',
            'role' => 'role',
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
            'name.required' => 'The name is required',
            'email.required' => 'The email address is required',
            'email.email' => 'The email must be a valid email address',
            'email.unique' => 'This email is already registered',
            'password.min' => 'The password must be at least 8 characters',
            'role.required' => 'The role is required',
            'role.in' => 'The role must be one of: admin, country_admin, global_admin',
        ];
    }
}

