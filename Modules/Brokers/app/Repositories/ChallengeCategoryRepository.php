<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\ChallengeCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ChallengeCategoryRepository
{
    protected ChallengeCategory $model;

    public function __construct(ChallengeCategory $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated challenge categories with filters
     */
    public function getChallengeCategories(Request $request): LengthAwarePaginator|Collection
    {
        $query = $this->model->newQuery();
        
        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        // Always load relationships
        $query->with(['steps', 'amounts']);

        if ($request->has('per_page') || $request->has('page')) {
            // Paginate with specific page
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);
            return $query->paginate($perPage, ['*'], 'page', $page);
        } else {
            return $query->get();
        }
    }

    /**
     * Get challenge category by ID with relations
     */
    public function findById(int $id): ?ChallengeCategory
    {
        return $this->model->with(['steps', 'amounts'])->find($id);
    }

    /**
     * Get challenge category by ID without relations
     */
    public function findByIdWithoutRelations(int $id): ?ChallengeCategory
    {
        return $this->model->find($id);
    }

    /**
     * Create new challenge category
     */
    public function create(array $data): ChallengeCategory
    {
        return $this->model->create($data);
    }

    /**
     * Update challenge category
     */
    public function update(ChallengeCategory $challengeCategory, array $data): bool
    {
        return $challengeCategory->update($data);
    }

    /**
     * Delete challenge category
     */
    public function delete(ChallengeCategory $challengeCategory): bool
    {
        return $challengeCategory->delete();
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters($query, Request $request): void
    {
        // Filter by name
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by is_active
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by created date
        if ($request->has('created_from')) {
            $query->where('created_at', '>=', $request->created_from);
        }

        if ($request->has('created_to')) {
            $query->where('created_at', '<=', $request->created_to);
        }
    }

    /**
     * Apply sorting to the query
     */
    protected function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Validate sort by field
        $allowedSortFields = [
            'id', 'name', 'is_active', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
