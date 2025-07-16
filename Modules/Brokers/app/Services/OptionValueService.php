<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\OptionValueRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\OptionValue;

class OptionValueService
{
    protected OptionValueRepository $repository;

    public function __construct(OptionValueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated option values with filters
     */
    public function getOptionValues(Request $request): array
    {
        try {
            $optionValues = $this->repository->getOptionValues($request);

            $response = [
                'success' => true,
                'data' => $optionValues,
            ];

            if ($request->has('per_page')) {
                $response['pagination'] = [
                    'current_page' => $optionValues->currentPage(),
                    'last_page' => $optionValues->lastPage(),
                    'per_page' => $optionValues->perPage(),
                    'total' => $optionValues->total(),
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('OptionValueService getOptionValues error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get option value by ID
     */
    public function getOptionValueById(int $id): ?OptionValue
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new option value
     */
    public function createOptionValue(array $data): OptionValue
    {
        return DB::transaction(function () use ($data) {
            try {
                // Create option value
                $optionValue = $this->repository->create($data);

                return $optionValue->load(['broker', 'option', 'zone', 'translations']);

            } catch (\Exception $e) {
                Log::error('OptionValueService createOptionValue error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Create multiple option values for a broker
     */
    public function createMultipleOptionValues(int $brokerId, array $optionValuesData): array
    {
        return DB::transaction(function () use ($brokerId, $optionValuesData) {
            try {
                // Validate input
                if (empty($optionValuesData)) {
                    throw new \InvalidArgumentException('Option values data cannot be empty');
                }

                // Prepare data for bulk insert
                $bulkData = [];
                $now = now();
                $options=BrokerOption::all()->pluck('id','slug');

               
                foreach ($optionValuesData as $index => $optionValueData) {
                    if (!is_array($optionValueData)) {
                        throw new \InvalidArgumentException("Option value data at index {$index} must be an array");
                    }
                    $slug=$optionValueData['option_slug'];
                    $optionValueData['broker_id'] = $brokerId;
                    $optionValueData['created_at'] = $now;
                    $optionValueData['updated_at'] = $now;
                    $optionValueData['broker_option_id']=$options[$slug];
                    
                    // Ensure metadata is JSON encoded
                    if (isset($optionValueData['metadata']) && is_array($optionValueData['metadata'])) {
                        $optionValueData['metadata'] = json_encode($optionValueData['metadata']);
                    }
                    
                    $bulkData[] = $optionValueData;
                }
               

                // Bulk insert all option values in one query
                $this->repository->bulkCreate($bulkData);
                
                // Get the created option values with relationships
                // Since bulk insert doesn't return IDs, we fetch by broker_id and option_slugs
                $optionSlugs = collect($optionValuesData)->pluck('option_slug')->toArray();
                $createdOptionValues = $this->repository->getByBrokerIdAndOptionSlugs($brokerId, $optionSlugs)
                    ->load(['broker', 'option', 'zone', 'translations']);

                // Ensure we return an array of arrays, not objects
                return $createdOptionValues->map(function ($optionValue) {
                    return $optionValue->toArray();
                })->toArray();

            } catch (\Exception $e) {
                Log::error('OptionValueService createMultipleOptionValues error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                throw $e;
            }
        });
    }

    /**
     * Update option value
     */
    public function updateOptionValue(int $id, array $data): OptionValue
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $optionValue = $this->repository->findByIdWithoutRelations($id);
                
                if (!$optionValue) {
                    throw new \Exception('Option value not found');
                }

                // Update option value
                $this->repository->update($optionValue, $data);

                return $optionValue->load(['broker', 'option', 'zone', 'translations']);

            } catch (\Exception $e) {
                Log::error('OptionValueService updateOptionValue error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update multiple option values for a broker
     */
    public function updateMultipleOptionValues(int $brokerId, array $optionValuesData): array
    {
        return DB::transaction(function () use ($brokerId, $optionValuesData) {
            try {
                $now = now();
                $updatesByCondition = [];
                $inserts = [];
                $options=BrokerOption::all()->pluck('id','slug');

                foreach ($optionValuesData as $optionValueData) {
                    // Ensure metadata is JSON encoded
                    if (isset($optionValueData['metadata']) && is_array($optionValueData['metadata'])) {
                        $optionValueData['metadata'] = json_encode($optionValueData['metadata']);
                    }
                    $optionValueData['updated_at'] = $now;

                    if (empty($optionValueData['id'])) {
                        // Remove id if present and null/empty
                        unset($optionValueData['id']);
                        $optionValueData['broker_id'] = $brokerId; // Ensure broker_id is set
                        $optionValueData['created_at'] = $now;
                        $optionValueData['broker_option_id']=$options[$optionValueData['option_slug']];
                        $inserts[] = $optionValueData;
                    } else {
                        $id = $optionValueData['id'];
                        unset($optionValueData['id']);
                        $updatesByCondition[$id] = $optionValueData;
                    }
                }

                if (config('app.debug')) {
                    Log::debug('Bulk Update Data:', ['updatesByCondition' => $updatesByCondition]);
                    Log::debug('Bulk Insert Data:', ['inserts' => $inserts]);
                }

                // Bulk update
                if (!empty($updatesByCondition)) {
                    $this->repository->bulkUpdate($updatesByCondition, $brokerId);
                }

                // Bulk insert
                if (!empty($inserts)) {
                    $this->repository->bulkCreate($inserts);
                }

                // Get all affected option values (updated + inserted)
                $updatedIds = array_keys($updatesByCondition);
                $inserted = collect();
                if (!empty($inserts)) {
                    // Optionally, fetch the newly inserted records if you need to return them
                    // This assumes you have enough info in $inserts to re-query them
                    // For example, by unique fields or by created_at timestamp
                    $inserted = OptionValue::where('broker_id', $brokerId)
                        ->where('created_at', $now)
                        ->get();
                       
                }
                $updated = !empty($updatedIds)
                    ? $this->repository->getByBrokerIdAndIds($brokerId, $updatedIds)
                    : collect();

                $all = $updated->merge($inserted)->load(['broker', 'option', 'zone', 'translations']);

                return $all->map(function ($optionValue) {
                    return $optionValue->toArray();
                })->toArray();

            } catch (\Exception $e) {
                Log::error('OptionValueService updateMultipleOptionValues error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                throw $e;
            }
        });
    }

    /**
     * Delete option value
     */
    public function deleteOptionValue(int $id): bool
    {
        try {
            $optionValue = $this->repository->findByIdWithoutRelations($id);
            
            if (!$optionValue) {
                throw new \Exception('Option value not found');
            }

            return $this->repository->delete($optionValue);

        } catch (\Exception $e) {
            Log::error('OptionValueService deleteOptionValue error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate option value data
     */
    public function validateOptionValueData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'id' => 'nullable|exists:option_values,id',
            'option_slug' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'value' => $isUpdate ? 'sometimes|required|string' : 'required|string',
            'public_value' => 'nullable|string',
            'status' => 'nullable|boolean',
            'status_message' => 'nullable|string|max:1000',
            'default_loading' => 'nullable|boolean',
            'type' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
            'is_invariant' => 'nullable|boolean',
            'delete_by_system' => 'nullable|boolean',
           // 'broker_id' => $isUpdate ? 'sometimes|required|exists:brokers,id' : 'required|exists:brokers,id',
           // 'broker_option_id' => $isUpdate ? 'sometimes|required|exists:broker_options,id' : 'required|exists:broker_options,id',
            'zone_id' => 'nullable|exists:zones,id',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        $validatedData = $validator->validated();
        
        // Ensure boolean fields are properly cast
        if (isset($validatedData['status'])) {
            $validatedData['status'] = (bool) $validatedData['status'];
        }
        if (isset($validatedData['default_loading'])) {
            $validatedData['default_loading'] = (bool) $validatedData['default_loading'];
        }
        if (isset($validatedData['is_invariant'])) {
            $validatedData['is_invariant'] = (bool) $validatedData['is_invariant'];
        }
        if (isset($validatedData['delete_by_system'])) {
            $validatedData['delete_by_system'] = (bool) $validatedData['delete_by_system'];
        }

        return $validatedData;
    }

    /**
     * Validate multiple option values data
     */
    public function validateMultipleOptionValuesData(array $optionValuesData, bool $isUpdate = false): array
    {
        $validatedData = [];
        
        foreach ($optionValuesData as $index => $optionValueData) {
            try {
                $validatedData[] = $this->validateOptionValueData($optionValueData, $isUpdate);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Option value at index {$index}: " . $e->getMessage());
            }
        }
        
        return $validatedData;
    }

    /**
     * Get form data for creating/editing option values
     */
    public function getFormData(): array
    {
        try {
            // Get brokers for dropdown
            $brokers = $this->repository->getBrokersForForm();
            
            // Get broker options for dropdown
            $brokerOptions = $this->repository->getBrokerOptionsForForm();
            
            // Get zones for dropdown
            $zones = $this->repository->getZonesForForm();

            return [
                'brokers' => $brokers,
                'broker_options' => $brokerOptions,
                'zones' => $zones,
                'status_options' => [
                    true => 'Active',
                    false => 'Inactive'
                ],
                'type_options' => [
                    'text' => 'Text',
                    'number' => 'Number',
                    'boolean' => 'Boolean',
                    'select' => 'Select',
                    'multiselect' => 'Multi Select'
                ]
            ];

        } catch (\Exception $e) {
            Log::error('OptionValueService getFormData error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get option values by broker ID
     */
    public function getByBrokerId(int $brokerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByBrokerId($brokerId);
    }

    /**
     * Get option values by broker option ID
     */
    public function getByBrokerOptionId(int $brokerOptionId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByBrokerOptionId($brokerOptionId);
    }

    /**
     * Get option values by status
     */
    public function getByStatus(bool $status): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByStatus($status);
    }

    /**
     * Search option values by value
     */
    public function searchByValue(string $search): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->searchByValue($search);
    }
} 