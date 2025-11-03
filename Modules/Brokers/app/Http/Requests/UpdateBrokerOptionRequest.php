<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrokerOptionRequest extends FormRequest
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
        $brokerOptionId = $this->route('id');
        
        return [
            'name' => 'sometimes|required|string|max:100',
            'slug' => "sometimes|string|max:100|unique:broker_options,slug,{$brokerOptionId}",
            'applicable_for' => 'sometimes|string|max:255',
            'data_type' => 'sometimes|string|max:100',
            'form_type' => 'sometimes|string|max:200',
            'meta_data' => 'nullable|string|max:10000',
            'for_crypto' => 'sometimes|boolean',
            'for_brokers' => 'sometimes|boolean',
            'for_props' => 'sometimes|boolean',
            'required' => 'sometimes|boolean',
            'placeholder' => 'nullable|string|max:100',
            'tooltip' => 'nullable|string|max:500',
            'min_constraint' => 'nullable|string|max:100',
            'max_constraint' => 'nullable|string|max:100',
            'load_in_dropdown' => 'nullable|boolean',
            'default_loading' => 'nullable|boolean',
            'default_loading_position' => 'nullable|integer|min:1',
            'dropdown_position' => 'nullable|integer|min:1',
            'position_in_category' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'allow_sorting' => 'nullable|boolean',
            'category_name' => 'sometimes|integer|exists:option_categories,id',
            'dropdown_list_attached' => 'nullable|integer|exists:dropdown_categories,id',
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
            'category_name' => 'category name',
            'dropdown_list_attached' => 'dropdown list attached',
            'position_in_category' => 'position in category',
            'default_loading_position' => 'default loading position',
            'is_active' => 'is active',
        ];
    }
}

