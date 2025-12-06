<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;
use Modules\Brokers\Tables\DropdownListTableConfig;
class DropdownListRequest extends BaseRequest
{
    protected function tableConfigClass(): string
    {
        return DropdownListTableConfig::class;
    }
    protected function formConfigClass(): ?string
    {
        return null;
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
        // return $rules;
        //     'per_page' => 'nullable|integer|min:1|max:100',
        //     'page' => 'nullable|integer|min:1',
        //     'order_by' => 'nullable|string|in:name,slug,created_at,updated_at',
        //     'order_direction' => 'nullable|string|in:asc,desc',
        //     'slug' => 'nullable|string|max:255',
        //     'name' => 'nullable|string|max:255',
        //     'description' => 'nullable|string|max:1000',
        // ];
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
            'sort_by' => 'sort column',
            'sort_direction' => 'sort direction',
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
            'sort_by.in' => 'Invalid sort column. Allowed columns: name, slug, created_at, updated_at',
            'sort_direction.in' => 'Invalid sort direction. Allowed values: asc, desc',
        ];
    }
}

