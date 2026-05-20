<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteEvaluationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'broker_id' => (int) $this->route('broker_id'),
            'id' => (int) $this->route('id'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'broker_id' => ['required', 'integer', 'exists:brokers,id'],
            'id' => [
                'required',
                'integer',
                Rule::exists('broker_evaluations', 'id')->where(
                    fn ($query) => $query->where('broker_id', $this->integer('broker_id'))
                ),
            ],
        ];
    }
}
