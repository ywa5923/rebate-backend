<?php

namespace Modules\Brokers\Http\Requests;


use Modules\Brokers\Forms\CountryForm;
use App\Http\Requests\BaseRequest;
use App\Forms\Form;
class UpdateCountryRequest extends BaseRequest
{
    protected function tableConfigClass(): ?string
    {
        return null;
    }
    protected function formConfigClass(): ?string
    {
        return CountryForm::class;
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
        $countryId = $this->route('country'); // Get country ID from route parameter (apiResource uses singular name)
        $formConfig = $this->getFormConfig();
        $constraints = $formConfig?->getFormConstraints(Form::MODE_UPDATE, $countryId) ?? [];
       
        return $constraints;
        // $countryId = $this->route('country'); // Get country ID from route parameter (apiResource uses singular name)
        
        // return [
        //     'name' => 'sometimes|required|string|max:255',
        //     'country_code' => [
        //         'sometimes',
        //         'required',
        //         'string',
        //         'max:100',
        //         Rule::unique('countries', 'country_code')->ignore($countryId),
        //     ],
          
        //     'zone_id' => 'sometimes|required|integer|exists:zones,id',
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

