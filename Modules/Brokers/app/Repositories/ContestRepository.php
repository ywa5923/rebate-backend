<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brokers\DTOs\ContestFilters;
use Modules\Brokers\Models\Contest;

class ContestRepository
{
    protected Contest $model;

    public function __construct(Contest $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated contests with filters
     * @param ContestFilters $filters
     * @param int $broker_id
     * @return LengthAwarePaginator|Collection
     * @throws \Exception
     */
    public function getContests(
        ContestFilters $filters,
        int $broker_id,
    ): LengthAwarePaginator|Collection {
        $query = $this->model->newQuery()->where('broker_id', $broker_id);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $this->applySorting($query, $filters);

        if ($filters->base->perPage || $filters->base->page) {
            // Paginate with specific page
            $perPage = $filters->base->perPage;
            $page = $filters->base->page;

            return $query->paginate($perPage, ['*'], 'page', $page);
        } else {
            return $query->get();
        }
    }

    /**
     * Get contest by ID with relations
     * @param int $id
     * @return Contest|null
     * @throws \Exception
     */
    public function findById(int $id): ?Contest
    {
        return $this->model->with(['broker'])->find($id);
    }

    /**
     * Get contest by ID without relations
     * @param int $id
     * @return Contest|null
     * @throws \Exception
     */
    public function findByIdWithoutRelations(int $id): ?Contest
    {
        return $this->model->find($id);
    }

    /**
     * Create new contest
     * @param array $data
     * @return Contest
     * @throws \Exception
     */
    public function create(array $data): Contest
    {
        return $this->model->create($data);
    }

    /**
     * Update contest
     * @param Contest $contest
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function update(Contest $contest, array $data): bool
    {
        return $contest->update($data);
    }

    /**
     * Delete contest
     * @param Contest $contest
     * @return bool
     * @throws \Exception
     */
    public function delete(Contest $contest): bool
    {
        return $contest->delete();
    }

    /**
     * Apply filters to the query
     * @param Builder $query
     * @param ContestFilters $filters
     * @throws \Exception
     */
    protected function applyFilters(Builder $query, ContestFilters $filters): void
    {
        // Filter by broker ID
        // if ($request->has('broker_id')) {
        //     $query->where('broker_id', $request->broker_id);
        // }

        if ($filters->contestId) {
            $query->where('id', $filters->contestId);
        }
        $withArray = [];

        if ($filters->base->zoneCode) {
            $withArray['optionValues'] = function ($q) use ($filters) {
                $q->where(function ($subQ) use ($filters) {
                    $subQ
                        ->where('is_invariant', 1)
                        ->orWhere('zone_code', $filters->base->zoneCode);
                });
            };
        } else {
            //if zone_code is not provided, we need to get the option values
            //for the account type that have no zone_code and zone_id, i.e original data submitted by broker
            $withArray['optionValues'] = function ($q) {
                $q->where(function ($subQ) {
                    $subQ->where('zone_code', null)->where('zone_id', null);
                });
            };
        }

        if ($filters->base->languageCode) {
            $withArray['optionValues.translations'] = function ($q) use (
                $filters,
            ) {
                $q->where('language_code', $filters->base->languageCode);
            };
        }

        if (! empty($withArray)) {
            $query->with($withArray);
        } else {
            $query->with(['broker', 'optionValues']);
        }
    }

    /**
     * Apply sorting to the query
     * @param Builder $query
     * @param ContestFilters $filters
     * @throws \Exception
     */
    protected function applySorting(Builder $query, ContestFilters $filters): void
    {
        $sortBy = $filters->base->sortBy ?? 'created_at';
        $sortDirection = $filters->base->sortDirection ?? 'asc';

        // Validate sort direction
        if (! in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Validate sort by field
        $allowedSortFields = ['id', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
