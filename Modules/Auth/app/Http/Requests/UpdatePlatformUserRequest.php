<?php

namespace Modules\Auth\Http\Requests;

use App\Http\Requests\BaseRequest;
use Modules\Auth\Forms\PlatformUserForm;
use App\Forms\Form;
class UpdatePlatformUserRequest extends BaseRequest
{
    protected function formConfigClass(): string
    {
        return PlatformUserForm::class;
    }
    protected function tableConfigClass(): ?string
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
        $userId = $this->route('platform_user');
        $formConfig = $this->getFormConfig();
        $constraints = $formConfig?->getFormConstraints(Form::MODE_UPDATE, $userId) ?? [];
       
        return $constraints;
       
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

