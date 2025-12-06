<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;
use Modules\Brokers\Forms\ZoneForm;
class StoreZoneRequest extends BaseRequest
{
    protected function tableConfigClass(): ?string
    {
        return null;
    }
    protected function formConfigClass(): ?string
    {
        return ZoneForm::class;
    }
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
        $formConfig = $this->getFormConfig();
        $constraints = $formConfig?->getFormConstraints() ?? [];
        return $constraints;
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'zone_code' => 'zone code',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The zone name is required',
            'zone_code.required' => 'The zone code is required',
            'zone_code.unique' => 'This zone code already exists',
            'zone_code.max' => 'The zone code cannot be longer than 100 characters',
        ];
    }
}

