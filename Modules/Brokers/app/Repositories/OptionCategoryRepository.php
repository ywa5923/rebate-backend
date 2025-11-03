<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Models\OptionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OptionCategoryRepository
{
    protected OptionCategory $model;

    public function __construct(OptionCategory $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated option categories with filters
     */
    public function getOptionCategories(Request $request): LengthAwarePaginator|Collection
    {
        $query = $this->model->newQuery();

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        if ($request->has('per_page') || $request->has('page')) {
            // Paginate with specific page
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);
            return $query->paginate($perPage, ['*'], 'page', $page);
        } else {
            $return=$query->get();
            return $return;
        }
       
    }

    /**
     * Get option categories list without relations
     */
    public function getOptionCategoriesList(array $filters = [], string $orderBy = 'id', string $orderDirection = 'asc', int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'like', "%{$filters['description']}%");
        }

        if (!empty($filters['slug'])) {
            $query->where('slug', 'like', "%{$filters['slug']}%");
        }

        // Apply sorting
        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get option category by ID with relations
     */
    public function findById(int $id): ?OptionCategory
    {
        return $this->model->with(['options', 'translations'])->find($id);
    }

    /**
     * Get option category by ID without relations
     */
    public function findByIdWithoutRelations(int $id): ?OptionCategory
    {
        return $this->model->find($id);
    }

    /**
     * Create new option category
     */
    public function create(array $data): OptionCategory
    {
        return $this->model->create($data);
    }

    /**
     * Update option category
     */
    public function update(OptionCategory $optionCategory, array $data): bool
    {
        return $optionCategory->update($data);
    }

    /**
     * Delete option category
     */
    public function delete(OptionCategory $optionCategory): bool
    {
        return $optionCategory->delete();
    }

    /**
     * Get option categories by status
     */
    public function getByStatus(bool $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Search option categories by name
     */
    public function searchByName(string $search): Collection
    {
        return $this->model->where('name', 'like', "%{$search}%")->get();
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        $withArray = [];
        
        // Add options relationship with broker type filtering
        $withArray['options'] = function($q) use ($request) {
            $this->applyBrokerTypeFilter($q, $request->broker_type ?? null);
        };
        
        // Add translations if language_code is provided
        if ($request->has('language_code')) {
            $withArray['translations'] = function($q) use ($request) {
                $q->where('language_code', $request->language_code);
            };
            
            $withArray['options.translations'] = function($q) use ($request) {
                $q->where('language_code', $request->language_code);
            };
        }

        
        
        $query->with($withArray);
        
     
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

       
       
    }

    /**
     * Apply broker type filter to options query
     */
    private function applyBrokerTypeFilter($query, ?string $brokerType): void
    {
        if (!$brokerType) {
            return;
        }

        $filterMap = [
            'broker' => 'for_brokers',
            'props' => 'for_props', 
            'crypto' => 'for_crypto'
        ];

        if (isset($filterMap[$brokerType])) {
            $query->where($filterMap[$brokerType], 1);
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'position');
        $sortDirection = $request->get('sort_direction', 'asc');

        $allowedSortFields = ['name', 'position', 'default_language', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }
}