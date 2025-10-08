<?php

namespace Modules\Brokers\Services;

use App\Utilities\ModelHelper;
use Modules\Brokers\Repositories\OptionValueRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\OptionValue;
use Modules\Brokers\Repositories\MatrixHeaderRepository;
use Modules\Brokers\Models\MatrixHeader;

class OptionValueService
{
    

    public function __construct(protected OptionValueRepository $repository,protected MatrixHeaderRepository $matrixHeaderRepository)
    {
       
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

    public function getModelClassFromSlug(string $slug): string
    {
        return ModelHelper::getModelClassFromSlug($slug);
    }


    /**
     * Save a model instance
     * @param string $modelClass
     * @param int $brokerId
     * @return int|null
     * @throws \InvalidArgumentException if class does not exist
     */
    public function saveModelInstance(string $modelClass,$brokerId): int|null
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class {$modelClass} not found");
        }
        
        // For Broker model, return the brokerId directly since we're not creating a new broker
        if ($modelClass === 'Modules\Brokers\Models\Broker') {
            return $brokerId;
        }
        
        // For other models (like Company, AccountType, etc.), create with broker_id
        $instance = $modelClass::create([
            'broker_id' => $brokerId,
            //'name' => 'New Company',
        ]);
        return $instance->id;  
      
    }

    public function createMatrixHeader(array $data): MatrixHeader
    {
        return $this->matrixHeaderRepository->create($data);
    }

    /**
     * Create multiple option values for a broker
     */
    public function createMultipleOptionValues(int $brokerId, string $modelClass, int $entityId, array $optionValuesData): void
    {
            try {
                // Validate input
                if (empty($optionValuesData)) {
                    throw new \InvalidArgumentException('Option values data cannot be empty');
                }

                // Prepare data for bulk insert
                $bulkData = [];
                $now = now();
                $options=BrokerOption::all()->pluck('id','slug');
              
            //    if($entityType && strtolower($entityType)!='broker'){
            //    // $className = ucfirst($entityType); // Convert 'company' to 'Company'
            //     $modelClass = ModelHelper::getModelClassFromSlug($entityType);
               
            //     if (!class_exists($modelClass)) {
            //         throw new \InvalidArgumentException("Model class {$modelClass} not found");
            //     }
                
            //     $instance = $modelClass::create([
            //         'broker_id' => $brokerId,
            //         //'name' => 'New Company',
            //     ]);
            //     $entityId = $instance->id;
              
            //     }else{
            //         //if entity type is null, it means that is a broker option value
            //         //so we need to create a new broker option value
            //         //and we need to set the entity id to the broker id
            //         $entityId=$brokerId;
            //         $modelClass=ModelHelper::getModelClassFromSlug('Broker');
            //     }
               
                foreach ($optionValuesData as $index => $optionValueData) {
                    if (!is_array($optionValueData)) {
                        throw new \InvalidArgumentException("Option value data at index {$index} must be an array");
                    }
                    $slug=$optionValueData['option_slug'];
                    $optionValueData['broker_id'] = $brokerId;
                    $optionValueData['created_at'] = $now;
                    $optionValueData['updated_at'] = $now;
                    $optionValueData['broker_option_id']=$options[$slug];
                    $optionValueData['optionable_id']=$entityId;
                    $optionValueData['optionable_type']=$modelClass;

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

                // $optionSlugs = collect($optionValuesData)->pluck('option_slug')->toArray();
                // $createdOptionValues = $this->repository->getByBrokerIdAndOptionSlugs($brokerId, $optionSlugs)
                //     ->load(['broker', 'option', 'zone', 'translations']);

                // // Ensure we return an array of arrays, not objects
                // return $createdOptionValues->map(function ($optionValue) {
                //     return $optionValue->toArray();
                // })->toArray();

            } catch (\Exception $e) {
                Log::error('OptionValueService createMultipleOptionValues error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                throw $e;
            }
        
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
    public function updateMultipleOptionValues(bool $isAdmin,int $brokerId,int $entity_id, string $entity_type, array $optionValuesData): array
    {
        //dd($optionValuesData);

        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class {$modelClass} not found");
        }
        //$optionValuesData is an array of option values data with the following structure:[['option_slug' => 'option_value','metadata' => 'metadata'],['option_slug' => 'option_value','metadata' => 'metadata']]
        
       
        return DB::transaction(function () use ($brokerId, $optionValuesData,$entity_id,$modelClass,$isAdmin) {
            try {
                $now = now();
                $updatesByCondition = [];
                $inserts = [];
                $options=BrokerOption::all()->pluck('id','slug');

                // Extract IDs that are different from 0 for comparison
                $idsToCompare = array_filter(array_column($optionValuesData, 'id'), function($id) {
                    return !empty($id) && $id != 0;
                });

                // Get existing option values for comparison
                $existingOptionValues = [];
                if (!empty($idsToCompare)) {
                    $existingOptionValues = OptionValue::whereIn('id', $idsToCompare)
                        ->get()
                        ->keyBy('id');
                }

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
                        $optionValueData['optionable_id']=$entity_id;
                        $optionValueData['optionable_type']=$modelClass;
                        $optionValueData['created_at'] = $now;
                        $optionValueData['broker_option_id']=$options[$optionValueData['option_slug']];
                        $inserts[] = $optionValueData;
                    } else {
                        // Check if this is an existing option value that needs comparison
                        if (!$isAdmin && isset($existingOptionValues[$optionValueData['id']])) {
                            $existingValue = $existingOptionValues[$optionValueData['id']];
                            
                            // Compare values to determine if they've changed
                            $valueChanged = $this->hasValueChanged($existingValue, $optionValueData);
                            
                            if ($valueChanged) {
                                // Set previous_value to old value and is_updated_entry to 1
                                $optionValueData['previous_value'] = $existingValue->value;
                                $optionValueData['is_updated_entry'] = 1;
                            } else {
                                // Keep existing previous_value and is_updated_entry
                                $optionValueData['previous_value'] = $existingValue->previous_value;
                                $optionValueData['is_updated_entry'] = $existingValue->is_updated_entry;
                            }
                        }else if($isAdmin){
                            // if(isset($optionValueData['is_updated_entry'])){
                            //     $optionValueData['is_updated_entry'] = 1;
                            // }else{
                            //     $optionValueData['is_updated_entry'] = 0;
                            // }

                        }
                        
                        $id = $optionValueData['id'];
                        unset($optionValueData['id']);
                        $updatesByCondition[$id] = $optionValueData;
                    }
                }

                // if (config('app.debug')) {
                //     Log::debug('Bulk Update Data:', ['updatesByCondition' => $updatesByCondition]);
                //     Log::debug('Bulk Insert Data:', ['inserts' => $inserts]);
                // }

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

    public function deleteOptionValuesByOptionableId(int $optionableId, string $optionableType): bool
    {
        $optionValues = $this->repository->getByOptionableId($optionableId,$optionableType);
        foreach ($optionValues as $optionValue) {
            $this->repository->delete($optionValue);
        }
        return true;
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

    public function validateEntityTypeAndId(string $entityType, int $entityId,int $brokerId,bool $isAdmin): bool
    {
        $allowedEntityTypes = ['broker', 'account-type','contest','promotion','company'];
        $modelClass = ModelHelper::getModelClassFromSlug($entityType);
       
        $table = (new $modelClass)->getTable();
        if(!class_exists($modelClass)){
            throw new \InvalidArgumentException("Model class {$modelClass} not found");
        }
        
        $rules=[
            'entity_type' => 'required|in:'.implode(',', $allowedEntityTypes),
            'entity_id' => 'required|exists:'.$table.',id',
            'broker_id' => 'required|exists:brokers,id',
        ];
        
        $validator = Validator::make(['entity_type' => $entityType, 'entity_id' => $entityId, 'broker_id' => $brokerId], $rules);
        if($validator->fails()){
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        if(!$isAdmin && $entityType !== 'broker'){
            $belongs = $modelClass::query()
            ->whereKey($entityId)
            ->where('broker_id', $brokerId)
            ->exists();
            if(!$belongs){
                throw new \InvalidArgumentException("Entity {$entityType} with id {$entityId} does not belong to broker {$brokerId}");
            }
        }
        
        return true;
    }
    /**
     * Validate option value data
     */
    public function validateOptionValueData(array $data, bool $isUpdate = false): array
    {
       
        $rules = [
            'id' => 'nullable|exists:option_values,id',
            'option_slug' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'value' => $isUpdate ? 'sometimes|nullable' : 'required',
            'public_value' => 'sometimes|nullable',
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
            'is_updated_entry' => 'sometimes|nullable|boolean',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        $validatedData = $validator->validated();
        
        // Validate and convert value field
        if (array_key_exists('value', $validatedData)) {
            if (!is_null($validatedData['value']) && !is_string($validatedData['value']) && !is_numeric($validatedData['value'])) {
                throw new \InvalidArgumentException('The value field must be null, a string, or numeric.');
            }
            // Convert to string if not null
            if (!is_null($validatedData['value'])) {
                $validatedData['value'] = (string) $validatedData['value'];
            }
        }
        
        // Validate and convert public_value field
        if (array_key_exists('public_value', $validatedData)) {
            if (!is_null($validatedData['public_value']) && !is_string($validatedData['public_value']) && !is_numeric($validatedData['public_value'])) {
                throw new \InvalidArgumentException('The public value field must be null, a string, or numeric.');
            }
            // Convert to string if not null
            if (!is_null($validatedData['public_value'])) {
                $validatedData['public_value'] = (string) $validatedData['public_value'];
            }
        }
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
       // dd($optionValuesData);
        foreach ($optionValuesData as $index => $optionValueData) {
            try {
                $validatedData[] = $this->validateOptionValueData($optionValueData, $isUpdate);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Option value at index {$index}-{$optionValueData['option_slug']}: " . $e->getMessage());
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

    /**
     * Check if option value has changed by comparing key fields
     * 
     * @param OptionValue $existingValue
     * @param array $newData
     * @return bool
     */
    private function hasValueChanged(OptionValue $existingValue, array $newData): bool
    {
        // Compare the main value fields that indicate a change
        $fieldsToCompare = ['value', 'public_value', 'status', 'status_message', 'default_loading', 'type'];
        
        foreach ($fieldsToCompare as $field) {
            if (isset($newData[$field])) {
                $existingFieldValue = $existingValue->$field;
                $newFieldValue = $newData[$field];
                
                // Handle different data types
                if (is_array($existingFieldValue) && is_array($newFieldValue)) {
                    if (array_diff_assoc($existingFieldValue, $newFieldValue) !== [] || 
                        array_diff_assoc($newFieldValue, $existingFieldValue) !== []) {
                        return true;
                    }
                } elseif ($existingFieldValue != $newFieldValue) {
                    return true;
                }
            }
        }
        
        return false;
    }
} 