<?php

namespace Modules\Brokers\Http\Requests;

use App\Http\Requests\BaseRequest;
use Modules\Brokers\Forms\BrokerGroupForm;

class BrokerGroupStoreRequest extends BaseRequest
{
    protected function tableConfigClass(): ?string
    {
        return null;
    }

    protected function formConfigClass(): ?string
    {
        return BrokerGroupForm::class;
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

    public function attributes(): array
    {
        return [
            'name' => 'name',
            'description' => 'description',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required',
            'description.required' => 'The description is required',
        ];
    }
}
