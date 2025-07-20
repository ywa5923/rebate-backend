<?php
namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\OptionCategoryRepository;
use Modules\Brokers\Models\OptionCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OptionCategoryService
{
    protected OptionCategoryRepository $repository;

    public function __construct(OptionCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated option categories with filters
     */
    public function getOptionCategories(Request $request): array
    {
        try {
            $optionCategories = $this->repository->getOptionCategories($request);

            $response = [
                'success' => true,
                'data' => $optionCategories,
            ];

            if ($request->has('per_page')) {
                $response['pagination'] = [
                    'current_page' => $optionCategories->currentPage(),
                    'last_page' => $optionCategories->lastPage(),
                    'per_page' => $optionCategories->perPage(),
                    'total' => $optionCategories->total(),
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('OptionCategoryService getOptionCategories error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get option category by ID
     */
    public function getOptionCategoryById(int $id): ?OptionCategory
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new option category
     */
    public function createOptionCategory(array $data): OptionCategory
    {
        return DB::transaction(function () use ($data) {
            try {
                // Validate input
                $validatedData = $this->validateOptionCategoryData($data);

                // Create option category
                $optionCategory = $this->repository->create($validatedData);

                return $optionCategory->load(['options', 'translations']);

            } catch (\Exception $e) {
                Log::error('OptionCategoryService createOptionCategory error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update option category
     */
    public function updateOptionCategory(int $id, array $data): OptionCategory
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $optionCategory = $this->repository->findByIdWithoutRelations($id);
                
                if (!$optionCategory) {
                    throw new \Exception('Option category not found');
                }

                // Validate input
                $validatedData = $this->validateOptionCategoryData($data, true);

                // Update option category
                $this->repository->update($optionCategory, $validatedData);

                return $optionCategory->load(['options', 'translations']);

            } catch (\Exception $e) {
                Log::error('OptionCategoryService updateOptionCategory error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete option category
     */
    public function deleteOptionCategory(int $id): bool
    {
        try {
            $optionCategory = $this->repository->findByIdWithoutRelations($id);
            
            if (!$optionCategory) {
                throw new \Exception('Option category not found');
            }

            return $this->repository->delete($optionCategory);

        } catch (\Exception $e) {
            Log::error('OptionCategoryService deleteOptionCategory error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate option category data
     */
    public function validateOptionCategoryData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'name' => $isUpdate ? 'sometimes|required|string|max:100' : 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'background_color' => 'nullable|string|max:100',
            'border_color' => 'nullable|string|max:100',
            'text_color' => 'nullable|string|max:100',
            'font_weight' => 'nullable|string|max:100',
            'position' => 'nullable|integer|min:1',
            'default_language' => 'nullable|string|max:50',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * Get option categories by status
     */
    public function getByStatus(bool $status): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByStatus($status);
    }

    /**
     * Search option categories by name
     */
    public function searchByName(string $search): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->searchByName($search);
    }
}
