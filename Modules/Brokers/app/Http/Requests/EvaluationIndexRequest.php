<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
            'lang' => 'sometimes|string|max:10',
        ];
    }
}

