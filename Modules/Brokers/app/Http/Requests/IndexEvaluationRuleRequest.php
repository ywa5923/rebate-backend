<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexEvaluationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'broker_id' => $this->route('broker_id'),
            'lang' => $this->input('lang') ?? 'en',
            'zone_id' => $this->input('zone_id') ?? null,
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
            'broker_id' => 'required|integer|exists:brokers,id',
            'zone_id' => 'nullable|integer|exists:zones,id',
            'lang' => 'required|string|max:10',
        ];
    }
}
