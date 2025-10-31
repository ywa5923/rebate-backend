<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDropdownListRequest extends FormRequest
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
            'list_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'options' => 'sometimes|array|min:1',
            'options.*.id' => 'sometimes|integer|exists:dropdown_options,id',
            'options.*.label' => 'required_without:options.*.id|sometimes|string|max:255',
            'options.*.value' => 'required_without:options.*.id|sometimes|string|max:255',
            'options.*.order' => 'nullable|integer|min:0',
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
            'list_name' => 'list name',
            'options' => 'options',
            'options.*.id' => 'option id',
            'options.*.label' => 'option label',
            'options.*.value' => 'option value',
            'options.*.order' => 'option order',
        ];
    }
}
