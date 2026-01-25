<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\PromotionRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\Promotion;

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
    public function getPromotions(Request $request,int $broker_id): array
    {
        try {
            $promotions = $this->repository->getPromotions($request,$broker_id);

            $response = [
                'success' => true,
                'data' => $promotions,
            ];

            if ($request->has('per_page')) {
                $response['pagination'] = [
                    'current_page' => $promotions->currentPage(),
                    'last_page' => $promotions->lastPage(),
                    'per_page' => $promotions->perPage(),
                    'total' => $promotions->total(),
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('PromotionService getPromotions error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get promotion by ID
     */
    public function getPromotionById(int $id): ?Promotion
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new promotion
     */
    public function createPromotion(array $data): Promotion
    {
        return DB::transaction(function () use ($data) {
            try {
                // Validate data
                $validatedData = $this->validatePromotionData($data);
                
                // Create promotion
                $promotion = $this->repository->create($validatedData);

                return $promotion->load(['broker']);

            } catch (\Exception $e) {
                Log::error('PromotionService createPromotion error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update promotion
     */
    public function updatePromotion(int $id, array $data): Promotion
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $promotion = $this->repository->findByIdWithoutRelations($id);
                
                if (!$promotion) {
                    throw new \Exception('Promotion not found');
                }

                // Validate data
                $validatedData = $this->validatePromotionData($data, true);
                
                // Update promotion
                $this->repository->update($promotion, $validatedData);

                return $promotion->load(['broker']);

            } catch (\Exception $e) {
                Log::error('PromotionService updatePromotion error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete promotion
     */
    public function deletePromotion(int $id, $broker_id): bool
    {
        try {
            $promotion = $this->repository->findByIdWithoutRelations($id);
            
            if (!$promotion) {
                throw new \Exception('Promotion not found');
            }

            if ($promotion->broker_id != $broker_id) {
                throw new \Exception('You are not authorized to delete this promotion');
            }

            DB::beginTransaction();
            $this->repository->delete($promotion);
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PromotionService deletePromotion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate promotion data
     */
    public function validatePromotionData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'broker_id' => $isUpdate ? 'sometimes|required|exists:brokers,id' : 'required|exists:brokers,id',
            'title' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
            'promotion_type' => 'nullable|string|max:100',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_unit' => 'nullable|string|max:50',
            'terms_conditions' => 'nullable|string',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator->validated();
    }
} 