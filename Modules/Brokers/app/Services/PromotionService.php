<?php

namespace Modules\Brokers\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brokers\DTOs\PromotionFilters;
use Modules\Brokers\Models\Promotion;
use Modules\Brokers\Repositories\PromotionRepository;

class PromotionService
{
    protected PromotionRepository $repository;

    public function __construct(PromotionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated promotions with filters
     */
    public function getPromotions(PromotionFilters $filters, int $broker_id): Collection|LengthAwarePaginator
    {

        return $this->repository->getPromotions($filters, $broker_id);

    }

    /**
     * Get promotion by ID
     */
    public function getPromotionById(int $id): ?Promotion
    {
        return $this->repository->findById($id);
    }

    /**
     * Delete promotion
     */
    public function deletePromotion(int $id, int $broker_id): ?Promotion
    {

        $promotion = $this->repository->findByIdWithoutRelations($id);

        if ($promotion === null || $promotion->broker_id !== $broker_id) {
            return null;
        }

        $deleted = $this->repository->delete($promotion);

        if (! $deleted) {
            return null;
        }

        return $promotion;
    }
}
