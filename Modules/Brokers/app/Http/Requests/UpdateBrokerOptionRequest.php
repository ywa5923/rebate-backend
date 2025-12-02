<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Brokers\Form\BrokerOptionForm;
class UpdateBrokerOptionRequest extends FormRequest
{

    public function __construct(private BrokerOptionForm $formConfig)
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
        $brokerOptionId = $this->route('id');
        $constraints = $this->formConfig->getFormConstraints();
      
       //dd($constraints);
        return $constraints;
        // return [
        //     'name' => 'sometimes|required|string|max:100',
        //     'slug' => "sometimes|string|max:100|unique:broker_options,slug,{$brokerOptionId}",
        //     'applicable_for' => 'sometimes|string|max:255',
        //     'data_type' => 'sometimes|string|max:100',
        //     'form_type' => 'sometimes|string|max:200',
        //     'meta_data' => 'nullable|string|max:10000',
        //     'for_crypto' => 'sometimes|boolean',
        //     'for_brokers' => 'sometimes|boolean',
        //     'for_props' => 'sometimes|boolean',
        //     'required' => 'sometimes|boolean',
        //     'placeholder' => 'nullable|string|max:100',
        //     'tooltip' => 'nullable|string|max:500',
        //     'min_constraint' => 'nullable|numeric',
        //     'max_constraint' => 'nullable|numeric',
        //     'load_in_dropdown' => 'nullable|boolean',
        //     'default_loading' => 'nullable|boolean',
        //     'default_loading_position' => 'nullable|integer|min:0',
        //     'dropdown_position' => 'nullable|integer|min:0',
        //     'position_in_category' => 'nullable|integer|min:0',
        //     'is_active' => 'nullable|boolean',
        //     'allow_sorting' => 'nullable|boolean',
        //     'option_category_id' => 'nullable|integer|exists:option_categories,id',
        //     'dropdown_category_id' => 'nullable|integer|exists:dropdown_categories,id',
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
            'option_category_id' => 'option category id',
            'dropdown_category_id' => 'dropdown category id',
            'position_in_category' => 'position in category',
            'default_loading_position' => 'default loading position',
            'is_active' => 'is active',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //     $response = response()->json([
    //         'success' => false,
    //         'message' => 'Validation failed',
    //         'errors' => $validator->errors()->toArray()
    //     ], 422);

    //     throw new \Illuminate\Validation\ValidationException($validator, $response);
    // }

    /**
     * Get the proper response for a failed validation.
     *
     * @param  array<string, mixed>  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }
}

