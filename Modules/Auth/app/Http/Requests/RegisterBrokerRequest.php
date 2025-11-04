<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterBrokerRequest extends FormRequest
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
            'broker_type_id' => 'required|integer|exists:broker_types,id',
            'email' => [
                'required',
                'email',
                Rule::unique('broker_team_users', 'email'),
            ],
            'trading_name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:countries,id',
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
            'broker_type_id' => 'broker type',
            'country_id' => 'country',
            'trading_name' => 'trading name',
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
            'broker_type_id.required' => 'The broker type is required',
            'broker_type_id.exists' => 'The selected broker type does not exist',
            'email.required' => 'The email address is required',
            'email.email' => 'The email must be a valid email address',
            'email.unique' => 'This email is already registered',
            'trading_name.required' => 'The trading name is required',
            'trading_name.max' => 'The trading name cannot be longer than 255 characters',
            'country_id.required' => 'The country is required',
            'country_id.exists' => 'The selected country does not exist',
        ];
    }
}

