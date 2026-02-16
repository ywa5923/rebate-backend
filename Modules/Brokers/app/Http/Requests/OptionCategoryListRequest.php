<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptionCategoryListRequest extends FormRequest
{
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
        return [
            // Filter parameters
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'slug' => 'nullable|string|max:100',
            
            // Ordering parameters
            'order_by' => 'nullable|string|in:id,name,slug,position,description,created_at,updated_at',
            'order_direction' => 'nullable|string|in:asc,desc,ASC,DESC',
            
            // Pagination parameter
            'per_page' => 'nullable|integer|min:1|max:100',
            'broker_type' => 'sometimes|string|max:100',
            'zone_id' => 'sometimes|exists:zones,id',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'name',
            'description' => 'description',
            'slug' => 'slug',
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
        
        $filterKeys = [
            'name',
            'description',
            'slug',
        ];
        
        foreach ($filterKeys as $key) {
            if ($this->has($key) && $this->filled($key)) {
                $filters[$key] = $this->input($key);
            }
        }
        if($this->has('broker_type') && $this->filled('broker_type')) {
            $filters['broker_type'] = $this->input('broker_type');
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
