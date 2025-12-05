<?php

namespace Modules\Brokers\Http\Requests;

use App\Http\Requests\BaseRequest;
use Modules\Brokers\Tables\ZoneTableConfig;
use Modules\Brokers\Forms\ZoneForm;
class ZoneListRequest extends BaseRequest
{
    protected function tableConfigClass(): ?string
    {
        return ZoneTableConfig::class;
    }

    protected function formConfigClass(): ?string
    {
        return ZoneForm::class;
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
     
        $rules= [
            ...$filtersConstraints,
            //'order_by' => 'nullable|string|in:id,category_name,dropdown_list_attached,category_position,name,applicable_for,data_type,form_type,for_brokers,for_crypto,for_props,required,allow_sorting,default_loading,default_loading_position',
            'order_by' => 'nullable|string|in:'.implode(',', array_keys($sortableColumns)),
            'order_direction' => 'nullable|string|in:asc,desc,ASC,DESC',
            // Pagination parameter
            'per_page' => 'nullable|integer|min:1|max:1000',
        ];

        return $rules;
    }
    /**
     * Get the filters array from the request.
     */
    public function getFilters(): array
    {
        $filters = [];
        $tableConfig = $this->getTableConfig();
        $filtersConstraints = $tableConfig?->getFiltersConstraints() ?? [];
       
       
        foreach ($filtersConstraints as $key) {
            if ($this->has($key) && $this->filled($key)) {
                $filters[$key] = $this->input($key);
            }
        }
       
        return $filters;
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'per_page' => 'items per page',
            'order_by' => 'sort column',
            'order_direction' => 'sort direction',
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
            'per_page.max' => 'You cannot request more than 100 items per page',
            'order_by.in' => 'Invalid sort column. Allowed columns: id, name, zone_code, created_at, updated_at',
        ];
    }
}

