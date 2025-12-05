<?php

namespace Modules\Brokers\Http\Requests;

use App\Http\Requests\BaseRequest;
use Modules\Brokers\Tables\CountryTableConfig;
use Modules\Brokers\Forms\CountryForm;
class CountryListRequest extends BaseRequest
{
    protected function tableConfigClass(): ?string
    {
        return CountryTableConfig::class;
    }

    protected function formConfigClass(): ?string
    {
        return CountryForm::class;
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
        // return [
        //     'per_page' => 'nullable|integer|min:1',
        //     'order_by' => 'nullable|string|in:id,name,country_code,zone_code,created_at,updated_at',
        //     'order_direction' => 'nullable|string|in:asc,desc',
        //     'name' => 'nullable|string|max:255',
        //     'country_code' => 'nullable|string|max:100',
        //     'zone_code' => 'nullable|string|max:100|exists:zones,zone_code',
        // ];
        $tableConfig = $this->getTableConfig();
        $filtersConstraints = $tableConfig?->getFiltersConstraints() ?? [];
        $sortableColumns = $tableConfig?->getSortableColumns() ?? [];
     
        $rules= [
            ...$filtersConstraints,
            //'order_by' => 'nullable|string|in:id,category_name,dropdown_list_attached,category_position,name,applicable_for,data_type,form_type,for_brokers,for_crypto,for_props,required,allow_sorting,default_loading,default_loading_position',
            'order_by' => 'nullable|string|in:'.implode(',', array_keys($sortableColumns)),
            'order_direction' => 'nullable|string|in:asc,desc,ASC,DESC',
            // Pagination parameter
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
       
        return $rules;
    }
    /**
     * Get the filters array from the request.
     *
     * @return array
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
            'country_code' => 'country code',
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
            'order_by.in' => 'Invalid sort column. Allowed columns: id, name, country_code, zone, created_at, updated_at',
        ];
    }
    /**
     * Get the order by parameter from the request.
     *
     * @param string $default
     * @return string
     */
    public function getOrderBy(string $default = 'id'): string
    {
        return $this->input('order_by', $default);
    }

    /**
     * Get the order direction parameter from the request.
     *
     * @param string $default
     * @return string
     */
    public function getOrderDirection(string $default = 'asc'): string
    {
        return strtolower($this->input('order_direction', $default));
    }

    /**
     * Get the per page parameter from the request.
     *
     * @param int $default
     * @return int
     */
    public function getPerPage(int $default = 15): int
    {
        return (int) $this->input('per_page', $default);
    }
}

