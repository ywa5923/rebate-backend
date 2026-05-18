<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationIndexRequest extends FormRequest
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
        ]);
    }

    public function rules(): array
    {
        return [
            'broker_id' => 'required|integer|exists:brokers,id',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
            'lang' => 'sometimes|string|max:10',
        ];
    }
}

