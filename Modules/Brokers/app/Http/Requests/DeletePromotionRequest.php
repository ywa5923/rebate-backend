<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeletePromotionRequest extends FormRequest
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
            'id' => (int) $this->route('id'),
            'broker_id' => (int) $this->route('broker_id'),
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
            'id' => [
                'required',
                'integer',
                Rule::exists('promotions', 'id')->where(
                    fn ($query) => $query->where('broker_id', $this->integer('broker_id'))
                ),
            ],
        ];
    }
}
