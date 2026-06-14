<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOptionValueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'broker_id' => $this->route('broker_id'),
            'option_values' => $this->input('option_values', []),
            'entity_id' => $this->input('entity_id', 0),
            'entity_type' => $this->input('entity_type', null),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        // Example payload (JSON):
        // {
        //   "entity_id": 0,
        //   "entity_type": "AccountType",
        //   "option_values": [
        //     {
        //       "id": null,
        //       "option_slug": "amount",
        //       "value": "7",
        //       "metadata": { "unit": "USD" }
        //     },
        //     {
        //       "id": null,
        //       "option_slug": "list_position",
        //       "value": "4",
        //       "metadata": null
        //     }
        //   ]
        // }
        return [
            'broker_id' => 'required|exists:brokers,id',
            'entity_id' => 'nullable|integer|min:0',
            'entity_type' => 'nullable|string|max:255',
            'option_values' => 'required|array|min:1',
            'option_values.*' => 'array',
            'option_values.*.id' => 'nullable|integer|exists:option_values,id',
            'option_values.*.option_slug' => 'required|string|max:255',
            'option_values.*.value' => 'nullable',
            'option_values.*.metadata' => 'sometimes|nullable|array',
            'option_values.*.metadata.unit' => 'sometimes|nullable|string|max:255',
        ];
    }
}
