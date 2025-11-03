<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\DropdownCategory;
use Modules\Brokers\Models\DropdownOption;
use Illuminate\Support\Facades\DB;

class DropdownListRepository
{
    protected DropdownCategory $categoryModel;
    protected DropdownOption $optionModel;

    public function __construct(DropdownCategory $categoryModel, DropdownOption $optionModel)
    {
        $this->categoryModel = $categoryModel;
        $this->optionModel = $optionModel;
    }

    /**
     * Get paginated dropdown categories with filters
     */
    public function getLists(array $filters = [], string $orderBy = 'name', string $orderDirection = 'asc')
    {
        $query = $this->categoryModel->with(['dropdownOptions' => function($query) {
            $query->orderBy('order', 'asc');
        }]);

        

        if (!empty($filters['description'])) {
            $query->where('description', 'like', "%{$filters['description']}%");
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['slug'])) {
            $query->where('slug', 'like', "%{$filters['slug']}%");
        }
    
        // Apply ordering
        $allowedOrderBy = ['id', 'name', 'slug', 'description', 'created_at', 'updated_at'];
        if (in_array($orderBy, $allowedOrderBy)) {
            $query->orderBy($orderBy, $orderDirection);
        } else {
            // Default ordering if invalid column provided
            $query->orderBy('name', $orderDirection);
        }

        return $query;
    }

    /**
     * Get dropdown category by ID with relations
     */
    public function findCategoryById(int $id): ?DropdownCategory
    {
        return $this->categoryModel->with(['dropdownOptions' => function($query) {
            $query->orderBy('order', 'asc');
        }])->find($id);
    }

    /**
     * Get dropdown category by ID without relations
     */
    public function findCategoryByIdWithoutRelations(int $id): ?DropdownCategory
    {
        return $this->categoryModel->find($id);
    }

    /**
     * Create new dropdown category
     */
    public function createCategory(array $data): DropdownCategory
    {
        return $this->categoryModel->create($data);
    }

    public function deleteList(int $id): bool
    {
        return DB::transaction(function () use ($id) {     
            //delete the options first
            $this->optionModel->where('dropdown_category_id', $id)->delete();
            //then delete the category
            $this->categoryModel->where('id', $id)->delete();
            return true;
        });
    }

}

