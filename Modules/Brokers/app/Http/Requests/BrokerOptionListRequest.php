<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Brokers\Table\BrokerOptionTableConfig;
class BrokerOptionListRequest extends FormRequest
{
    public function __construct(private BrokerOptionTableConfig $tableConfig)
    {
        parent::__construct();
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
       
        $filtersConstraints = $this->tableConfig->getFiltersConstraints();
        $sortableColumns = $this->tableConfig->getSortableColumns();
     
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
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_name' => 'category name',
            'dropdown_category_name' => 'dropdown category name',
            'order_by' => 'order by',
            'order_direction' => 'order direction',
            'per_page' => 'per page',
        ];
    }

    /**
     * Get the filters array from the request.
     *
     * @return array
     */
    public function getFilters(): array
    {
        $filters = [];
        
        $filterKeys = array_keys($this->tableConfig->getFiltersConstraints());
       
        foreach ($filterKeys as $key) {
            if ($this->has($key) && $this->filled($key)) {
                $filters[$key] = $this->input($key);
            }
        }
       
        return $filters;
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
