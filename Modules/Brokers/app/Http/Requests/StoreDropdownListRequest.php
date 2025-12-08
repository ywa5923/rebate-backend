<?php

namespace Modules\Brokers\Http\Requests;



use Modules\Brokers\Forms\DropdownListForm;
use App\Http\Requests\BaseRequest;

class StoreDropdownListRequest extends BaseRequest
{
    protected function formConfigClass(): string
    {
        return DropdownListForm::class;
    }
    protected function tableConfigClass(): ?string
    {
        return null;
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
            'list_name' => 'list name',
            'options' => 'options',
            'options.*.label' => 'option label',
            'options.*.value' => 'option value',
            'options.*.order' => 'option order',
        ];
    }
}
