<?php

namespace Modules\Brokers\Http\Requests;



use Modules\Brokers\Forms\EvaluationForm;
use App\Http\Requests\BaseRequest;

class StoreEvaluationRuleRequest extends BaseRequest
{
    protected function formConfigClass(): string
    {
        return EvaluationForm::class;
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
        //produce something like this:
        //  [ 
        //     "copy-trading#1" => "string|required|exists:evaluation_options,id"
        //     "scalping#2" => "string|required|exists:evaluation_options,id"
        //     "hedging#3" => "string|required|exists:evaluation_options,id"
        //     "buy-back#4" => "string|required|exists:evaluation_options,id"
        //     "copy-trading#1_getteer" => "sometimes|string|max:255"
        //     "scalping#2_getteer" => "sometimes|string|max:255"
        //     "hedging#3_getteer" => "sometimes|string|max:255"
        //     "buy-back#4_getteer" => "sometimes|string|max:255"
        //   ]
        $formConfig = $this->getFormConfig();
        $constraints = $formConfig?->getFormConstraints() ?? [];

        $getters_constraints = [];
        foreach ($constraints as $key => $value) {
          $getters_constraints[$key.'_getter'] = "sometimes|nullable|string|max:1255|min:1";
        }
        //dd(array_merge($constraints, $getters_constraints));
       
        return array_merge($constraints, $getters_constraints);
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
