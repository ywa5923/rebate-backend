<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\ChallengeCategoryRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\ChallengeCategory;

class ChallengeCategoryService
{
    protected ChallengeCategoryRepository $repository;

    public function __construct(ChallengeCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated challenge categories with filters
     */
    public function getChallengeCategories(Request $request): array
    {
        try {
            $challengeCategories = $this->repository->getChallengeCategories($request);

            $response = [
                'success' => true,
                'data' => $challengeCategories,
            ];

            if ($request->has('per_page')) {
                $response['pagination'] = [
                    'current_page' => $challengeCategories->currentPage(),
                    'last_page' => $challengeCategories->lastPage(),
                    'per_page' => $challengeCategories->perPage(),
                    'total' => $challengeCategories->total(),
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('ChallengeCategoryService getChallengeCategories error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get challenge category by ID
     */
    public function getChallengeCategoryById(int $id): ?ChallengeCategory
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new challenge category
     */
    public function createChallengeCategory(array $data): ChallengeCategory
    {
        return DB::transaction(function () use ($data) {
            try {
                // Validate data
                $validatedData = $this->validateChallengeCategoryData($data);
                
                // Create challenge category
                $challengeCategory = $this->repository->create($validatedData);

                return $challengeCategory->load(['steps', 'amounts']);

            } catch (\Exception $e) {
                Log::error('ChallengeCategoryService createChallengeCategory error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update challenge category
     */
    public function updateChallengeCategory(int $id, array $data): ChallengeCategory
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $challengeCategory = $this->repository->findByIdWithoutRelations($id);
                
                if (!$challengeCategory) {
                    throw new \Exception('Challenge category not found');
                }

                // Validate data
                $validatedData = $this->validateChallengeCategoryData($data, true);
                
                // Update challenge category
                $this->repository->update($challengeCategory, $validatedData);

                return $challengeCategory->load(['steps', 'amounts']);

            } catch (\Exception $e) {
                Log::error('ChallengeCategoryService updateChallengeCategory error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete challenge category
     */
    public function deleteChallengeCategory(int $id): bool
    {
        try {
            $challengeCategory = $this->repository->findByIdWithoutRelations($id);
            
            if (!$challengeCategory) {
                throw new \Exception('Challenge category not found');
            }

            DB::beginTransaction();
            
            // Delete related steps and amounts first
            $challengeCategory->steps()->delete();
            $challengeCategory->amounts()->delete();
            
            // Delete challenge category
            $this->repository->delete($challengeCategory);
            
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ChallengeCategoryService deleteChallengeCategory error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate challenge category data
     */
    public function validateChallengeCategoryData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator->validated();
    }
}
