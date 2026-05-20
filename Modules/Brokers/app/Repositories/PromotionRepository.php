<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Promotion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\DTOs\PromotionFilters;
use Illuminate\Database\Eloquent\Builder;
class PromotionRepository
{
    protected Promotion $model;

    public function __construct(Promotion $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated promotions with filters
     * @param PromotionFilters $filters
     * @param int $broker_id
     * @return LengthAwarePaginator|Collection
     */
    public function getPromotions(PromotionFilters $filters,int $broker_id): LengthAwarePaginator|Collection
    {
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
     * Get promotion by ID with relations
     * @param int $id
     * @return Promotion|null
     */
    public function findById(int $id): ?Promotion
    {
        return $this->model->with(['broker'])->find($id);
    }

    /**
     * Get promotion by ID without relations
     * @param int $id
     * @return Promotion|null

     */
    public function findByIdWithoutRelations(int $id): ?Promotion
    {
        return $this->model->find($id);
    }

    

    /**
     * Delete promotion
     */
    public function delete(Promotion $promotion): bool
    {
        return $promotion->delete();
    }

    /**
     * Apply filters to the query
     * @param Builder $query
     * @param PromotionFilters $filters
     */
    protected function applyFilters(Builder $query, PromotionFilters $filters): void
    {
        // Filter by broker ID
        // if ($request->has('broker_id')) {
        //     $query->where('broker_id', $request->broker_id);
        // }

        if ($filters->promotionId) {
            $query->where('id', $filters->promotionId);
        }

        $withArray = [];

        if ($filters->base->zoneCode) {
            $withArray['optionValues'] = function ($q) use ($filters) {
                $q->where(function ($subQ) use ($filters) {
                    $subQ->where('is_invariant', 1)
                        ->orWhere('zone_code', $filters->base->zoneCode);
                });
            };
        }else{
            //if zone_code is not provided, we need to get the option values 
            //for the account type that have no zone_code and zone_id, i.e original data submitted by broker
            $withArray['optionValues'] = function ($q){
                $q->where(function ($subQ)  {
                    $subQ->where('zone_code', null)->where('zone_id',null);
                });
            };
        }

        if ($filters->base->languageCode) {
            $withArray['optionValues.translations'] = function ($q) use ($filters) {
                $q->where('language_code', $filters->base->languageCode);
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
     * @param Builder $query
     * @param PromotionFilters $filters
     */
    protected function applySorting(Builder $query, PromotionFilters $filters): void
    {
        $sortBy = $filters->base->sortBy ?? 'created_at';
        $sortDirection = $filters->base->sortDirection ?? 'asc';

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
            $query->orderBy('created_at', 'asc');
        }
    }
} 