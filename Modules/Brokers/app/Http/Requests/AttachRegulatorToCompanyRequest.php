<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachRegulatorToCompanyRequest extends FormRequest
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
            'company_id' => (int) $this->route('company_id'),
            'broker_id' => (int) $this->route('broker_id'),
            'regulator_id' => (int) $this->route('regulator_id'),
            'zone_id' => $this->input('zone_id'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'broker_id' => ['required', 'integer', 'exists:brokers,id'],
            'company_id' => [
                'required',
                'integer',
                Rule::exists('companies', 'id')->where(
                    fn ($query) => $query->where('broker_id', $this->integer('broker_id'))
                ),
            ],
            'regulator_id' => ['required', 'integer', 'exists:regulators,id'],
            'zone_id' => ['sometimes', 'nullable', 'integer', 'exists:zones,id'],
        ];
    }
}
