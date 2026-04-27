<?php

namespace Modules\Brokers\Http\Requests;

use App\Http\Requests\BaseRequest;
use Modules\Brokers\Forms\BrokerGroupForm;
use Modules\Brokers\Tables\BrokerGroupTableConfig;

class BrokerGroupListRequest extends BaseRequest
{
    protected function tableConfigClass(): ?string
    {
        return BrokerGroupTableConfig::class;
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

        $tableConfig = $this->getTableConfig();

        $filtersConstraints = $tableConfig?->getFiltersConstraints() ?? [];
        $sortableColumns = $tableConfig?->getSortableColumns() ?? [];

        $rules = [
            ...$filtersConstraints,
            //'order_by' => 'nullable|string|in:id,category_name,dropdown_list_attached,category_position,name,applicable_for,data_type,form_type,for_brokers,for_crypto,for_props,required,allow_sorting,default_loading,default_loading_position',
            'order_by' => 'nullable|string|in:'.implode(',', array_keys($sortableColumns)),
            'order_direction' => 'nullable|string|in:asc,desc,ASC,DESC',
            // Pagination parameter
            'per_page' => 'nullable|integer|min:1|max:1000',
        ];

        return $rules;
    }
}
