<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\PromotionRepository;
use Modules\Brokers\Models\Promotion;
use Modules\Brokers\DTOs\PromotionFilters;
use Modules\Brokers\Transformers\PromotionResource;

class PromotionService
{
    protected PromotionRepository $repository;

    public function __construct(PromotionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated promotions with filters
     * @param PromotionFilters $filters
     * @param int $broker_id
     * @return array
     * @throws \Exception
     */
    public function getPromotions(PromotionFilters $filters, int $broker_id): array
    {

        $promotions = $this->repository->getPromotions($filters, $broker_id);

        $response = [
            'success' => true,
            'data' => PromotionResource::collection($promotions),
        ];

        if ($filters->base->perPage || $filters->base->page) {
            $response['pagination'] = [
                'current_page' => $promotions->currentPage(),
                'last_page' => $promotions->lastPage(),
                'per_page' => $promotions->perPage(),
                'total' => $promotions->total(),
            ];
        }

        return $response;
    }

    /**
     * Get promotion by ID
     * @param int $id
     * @return Promotion|null
     */
    public function getPromotionById(int $id): ?Promotion
    {
        return $this->repository->findById($id);
    }


    /**
     * Delete promotion
     * @param int $id
     * @param int $broker_id
     * @return Promotion|null
     */
    public function deletePromotion(int $id, int $broker_id): ?Promotion
    {

        $promotion = $this->repository->findByIdWithoutRelations($id);

        if ($promotion === null || $promotion->broker_id !== $broker_id) {
            return null;
        }

        $deleted = $this->repository->delete($promotion);

        if (!$deleted) {
            return null;
        }
        return $promotion;
    }

}
