<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Contest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ContestRepository
{
    protected Contest $model;

    public function __construct(Contest $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated contests with filters
     */
    public function getContests(Request $request): LengthAwarePaginator|Collection
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
            return $query->get();
        }
    }

    /**
     * Get contest by ID with relations
     */
    public function findById(int $id): ?Contest
    {
        return $this->model->with(['broker'])->find($id);
    }

    /**
     * Get contest by ID without relations
     */
    public function findByIdWithoutRelations(int $id): ?Contest
    {
        return $this->model->find($id);
    }

    /**
     * Create new contest
     */
    public function create(array $data): Contest
    {
        return $this->model->create($data);
    }

    /**
     * Update contest
     */
    public function update(Contest $contest, array $data): bool
    {
        return $contest->update($data);
    }

    /**
     * Delete contest
     */
    public function delete(Contest $contest): bool
    {
        return $contest->delete();
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters($query, Request $request): void
    {
        // Filter by broker ID
        if ($request->has('broker_id')) {
            $query->where('broker_id', $request->broker_id);
        }

        if ($request->has('contest_id')) {
            $query->where('id', $request->contest_id);
        }
        $withArray = [];

        if ($request->has('zone_code')) {
            $withArray['optionValues'] = function ($q) use ($request) {
                $q->where(function ($subQ) use ($request) {
                    $subQ->where('is_invariant', 1)
                        ->orWhere('zone_code', $request->zone_code);
                });
            };
        }else{
            //if zone_code is not provided, we need to get the option values 
            //for the account type that have no zone_code and zone_id, i.e original data submitted by broker
            $withArray['optionValues'] = function ($q) use ($request) {
                $q->where(function ($subQ) use ($request) {
                    $subQ->where('zone_code', null)->where('zone_id',null);
                });
            };
        }

        if ($request->has('language_code')) {
            $withArray['optionValues.translations'] = function ($q) use ($request) {
                $q->where('language_code', $request->language_code);
            };
        }

        if (!empty($withArray)) {
            $query->with($withArray);
        } else {
            $query->with(['broker','optionValues']);
        }


        
        
        
    }

    /**
     * Apply sorting to the query
     */
    protected function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'asc');

        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Validate sort by field
        $allowedSortFields = [
            'id', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
} 