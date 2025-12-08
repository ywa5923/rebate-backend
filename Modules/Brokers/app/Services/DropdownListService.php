<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\DropdownListRepository;
use Modules\Brokers\Models\DropdownCategory;
use Modules\Brokers\Models\DropdownOption;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DropdownListService
{
    protected DropdownListRepository $repository;

    public function __construct(DropdownListRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated dropdown categories with filters
     */
    public function getLists(array $filters = [], string $orderBy = 'name', string $orderDirection = 'asc', int $perPage = 15)
    {
        $query = $this->repository->getLists($filters, $orderBy, $orderDirection);
        
        return $query->paginate($perPage);
    }

    /**
     * Get dropdown list by ID
     */
    public function getListById(int $id): ?DropdownCategory
    {
        return $this->repository->findCategoryById($id);
    }

    /**
     * Delete dropdown list by ID
     */
    public function deleteList(int $id): bool
    {
        return $this->repository->deleteList($id);
    }

    /**
     * Create new dropdown category with options
     */
    public function createList(array $data): DropdownCategory
    {
        return DB::transaction(function () use ($data) {
            try {
                // Generate slug from list_name
                $slug = \Illuminate\Support\Str::slug($data['name']);
                
                // Ensure unique slug
                $originalSlug = $slug;
                $counter = 1;
                while (DropdownCategory::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                // Create the category
                $categoryData = [
                    'name' => $data['name'],
                    'slug' => $slug,
                    'description' => $data['description'] ?? null,
                ];
                $category = $this->repository->createCategory($categoryData);

                // Create the options
                if (isset($data['options']) && is_array($data['options'])) {
                    $optionsData = [];
                    $order = 1;
                    foreach ($data['options'] as $option) {
                        $optionsData[] = [
                            'dropdown_category_id' => $category->id,
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'order' => $order,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $order++;
                    }
                    DropdownOption::insert($optionsData);
                }

                return $category->load(['dropdownOptions' => function($query) {
                    $query->orderBy('order', 'asc');
                }]);

            } catch (\Exception $e) {
                Log::error('DropdownListService createList error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update dropdown category with options
     */
    public function updateList(int $id, array $data): DropdownCategory
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $category = $this->repository->findCategoryByIdWithoutRelations($id);
                
                if (!$category) {
                    throw new \Exception('Dropdown category not found');
                }

                // Update category name if provided
                if (isset($data['name'])) {
                    $category->name = $data['name'];

                    // Generate new slug if name changed
                    $slug = \Illuminate\Support\Str::slug($data['name']);
                    $originalSlug = $slug;
                    $counter = 1;
                    while (DropdownCategory::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                        $slug = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                    $category->slug = $slug;
                }

                // Update description if provided
                if (isset($data['description'])) {
                    $category->description = $data['description'];
                }

                $category->save();
                
                // Refresh to get fresh data
                $category->refresh();

                // Update options if provided
                if (isset($data['options']) && is_array($data['options'])) {
                    // Get existing option IDs
                    $existingOptionIds = $category->dropdownOptions->pluck('id')->toArray();
                    
                    // Track processed option IDs
                    $processedOptionIds = [];
                    $optionsToInsert = [];
                    $optionsToUpdate = [];
                    
                    // Get the highest existing order to start new inserts from
                    $maxExistingOrder = $category->dropdownOptions->max('order') ?? 0;
                    $nextOrder = $maxExistingOrder + 1;

                    foreach ($data['options'] as $option) {
                        if (isset($option['id']) && in_array($option['id'], $existingOptionIds)) {
                            // Prepare for bulk update - preserve order from request or use existing
                            $optionsToUpdate[] = [
                                'id' => $option['id'],
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'order' => $option['order'] ?? $category->dropdownOptions->firstWhere('id', $option['id'])->order ?? 0,
                            ];
                            $processedOptionIds[] = $option['id'];
                        } else {
                            // New option to insert - use auto-incrementing order
                            $optionsToInsert[] = [
                                'dropdown_category_id' => $category->id,
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'order' => $nextOrder,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            $nextOrder++;
                        }
                    }

                    // Bulk update existing options using CASE statements
                    if (!empty($optionsToUpdate)) {
                        $updateIds = array_column($optionsToUpdate, 'id');
                        
                        $bindings = [];
                        $caseStatements = [];
                        $columns = ['label', 'value', 'order'];
                        
                        foreach ($columns as $column) {
                            $caseStatement = "CASE id ";
                            $hasValues = false;
                            
                            foreach ($optionsToUpdate as $optionData) {
                                $caseStatement .= "WHEN ? THEN ? ";
                                $bindings[] = $optionData['id'];
                                $bindings[] = $optionData[$column];
                                $hasValues = true;
                            }
                            
                            $caseStatement .= "END";
                            if ($hasValues) {
                                $caseStatements[] = "{$column} = {$caseStatement}";
                            }
                        }
                        
                        $sql = "UPDATE dropdown_options SET " . implode(', ', $caseStatements) . " WHERE id IN (" . implode(',', array_fill(0, count($updateIds), '?')) . ")";
                        $bindings = array_merge($bindings, $updateIds);
                        
                        DB::update($sql, $bindings);
                    }

                    // Insert new options
                    if (!empty($optionsToInsert)) {
                        DropdownOption::insert($optionsToInsert);
                    }

                    // Delete options that are no longer in the list
                    $optionsToDelete = array_diff($existingOptionIds, $processedOptionIds);
                    if (!empty($optionsToDelete)) {
                        DropdownOption::whereIn('id', $optionsToDelete)->delete();
                    }
                }

                return $category->load(['dropdownOptions' => function($query) {
                    $query->orderBy('order', 'asc');
                }]);

            } catch (\Exception $e) {
                Log::error('DropdownListService updateList error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

}

