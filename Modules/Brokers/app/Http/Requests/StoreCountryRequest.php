<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Brokers\Form\CountryForm;
class StoreCountryRequest extends FormRequest
{
    public function __construct(private CountryForm $formConfig)
    {
        parent::__construct();
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
        $constraints = $this->formConfig->getFormConstraints();
       
        return $constraints;
        // return [
        //     'name' => 'required|string|max:255',
        //     'country_code' => 'required|string|max:100|unique:countries,country_code',
           
        //     'zone_id' => 'required|integer|exists:zones,id',
        // ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'country_code' => 'country code',
            'zone_id' => 'zone',
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
            'name.required' => 'The country name is required',
            'country_code.required' => 'The country code is required',
            'country_code.unique' => 'This country code already exists',
            'country_code.max' => 'The country code cannot be longer than 100 characters',
            'zone_id.required' => 'The zone is required',
            'zone_id.exists' => 'The selected zone does not exist',
        ];
    }
}

