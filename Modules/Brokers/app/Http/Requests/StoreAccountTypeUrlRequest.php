<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountTypeUrlRequest extends FormRequest
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
            'broker_id' => (int) $this->route('broker_id'),
        ]);

        if ($this->route()->hasParameter('url_id')) {
            $this->merge([
                'id' => (int) $this->route('url_id'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'sometimes|nullable|integer|exists:urls,id',
            'url_type' => 'required|string|max:200',
            'url' => 'required|url',
            'name' => 'required|string|max:500',
            'account_type_id' => 'sometimes|nullable|integer|exists:account_types,id',
            'broker_id' => 'required|integer|exists:brokers,id',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
        ];
    }
}
