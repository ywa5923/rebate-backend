<?php

namespace Modules\Brokers\Services;

use App\Utilities\ModelHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Brokers\DTOs\OptionValueFilters;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\MatrixHeader;
use Modules\Brokers\Models\OptionValue;
use Modules\Brokers\Repositories\MatrixHeaderRepository;
use Modules\Brokers\Repositories\OptionValueRepository;
use App\Exceptions\ApiException;

class OptionValueService
{
    public function __construct(
        protected OptionValueRepository $repository,
        protected MatrixHeaderRepository $matrixHeaderRepository,
    ) {}

    /**
     * Get paginated option values with filters
     *
     * @return LengthAwarePaginator|Collection<int, OptionValue>
     */
    public function getOptionValues(OptionValueFilters $filters, int $broker_id): LengthAwarePaginator|Collection
    {
        return $this->repository->getOptionValues(
            $filters,
            $broker_id,
        );
    }

    /**
     * Get option value by ID
     */
    public function getOptionValueById(int $id): ?OptionValue
    {
        return $this->repository->findById($id);
    }


    public function getModelClassFromSlug(string $slug): string
    {
        return ModelHelper::getModelClassFromSlug($slug);
    }

    /**
     * Save a model instance
     *
     * @param  int  $brokerId
     *
     * @throws \InvalidArgumentException if class does not exist
     */
    public function saveModelInstance(string $modelClass, $brokerId): ?int
    {
        if (! class_exists($modelClass)) {
           
            throw new ApiException("Model class {$modelClass} not found", 400);
        }

        // For Broker model, return the brokerId directly since we're not creating a new broker
        if ($modelClass === "Modules\Brokers\Models\Broker") {
            return $brokerId;
        }

        // For other models (like Company, AccountType, etc.), create with broker_id
        $instance = $modelClass::create([
            'broker_id' => $brokerId,
            
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
    public function createMultipleOptionValues(
        int $brokerId,
        bool $isAdmin,
        string $modelClass,
        int $entityId,
        array $optionValuesData,
    ): void {
        // Validate input
        if (empty($optionValuesData)) {
           throw new ApiException("Option values data is empty", 400);
        }

        // Prepare data for bulk insert
        $bulkData = [];
        $now = now();
        $options = BrokerOption::all()->pluck('id', 'slug');

        foreach ($optionValuesData as $index => $optionValueData) {
            if (! is_array($optionValueData)) {
                    throw new ApiException("Option value data at index {$index} must be an array", 400);
            }
            $slug = $optionValueData['option_slug'];
            $optionValueData['broker_id'] = $brokerId;
            $optionValueData['created_at'] = $now;
            $optionValueData['updated_at'] = $now;
            $optionValueData['broker_option_id'] = $options[$slug];
            $optionValueData['optionable_id'] = $entityId;
            $optionValueData['optionable_type'] = $modelClass;
            $optionValueData['metadata'] = $this->formatMetadata($optionValueData['metadata']??null, $isAdmin);

            if ($isAdmin) {
                //when admin save the data copy value to public_value
                $optionValueData['public_value'] = $optionValueData['value'];
            }

            $bulkData[] = $optionValueData;
        }
        //transform jsonMetadata
        foreach ($bulkData as &$row) {
            if (is_array($row['metadata'] ?? null)) {
                $row['metadata'] = json_encode($row['metadata']);
            }
        }
        unset($row);

        // Bulk insert all option values in one query
        $this->repository->bulkCreate($bulkData);
    }


    /**
     * Format metadata for insert
     */
    public function formatMetadata(?array $metadata, bool $isAdmin): ?array
    {
        //Pentru numberWithUnit, fronendul trimite
        // {
        //     "option_slug": "minimum_trade_size2",
        //     "value": "100",
        //     "metadata": { "unit": "USD" }
        //}
        //Aceasta metoda returneaza:
        // {
        //     "public_value": { "unit": "USD" },
        //     "value":        { "unit": "USD" }
        // }
        //sau pt broker:
        // {
        //     "value": { "unit": "USD" },
        // }
        if (empty($metadata) || !is_array($metadata)) {
            return null;
        }
        //for types numberWithUnit, we need to add the unit to the metadata
        //unit stored in metadata also have publicvalue and value
        
        if ($isAdmin) {
            $metadata = [
                'public_value' => $metadata,
                'value' => $metadata
            ];
        } else {
            $metadata = [
                'value' => $metadata,
            ];
        }
        return $metadata;
    }

    /**
     * Update multiple option values for a broker
     */
    public function updateMultipleOptionValues(
        bool $isAdmin,
        int $brokerId,
        int $entity_id,
        string $entity_type,
        array $optionValuesData,
        ?int $zoneId = null,
    ): true {
        
        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);
        if (! class_exists($modelClass)) {
            throw new \InvalidArgumentException(
                "Model class {$modelClass} not found",
            );
        }
        //$optionValuesData is an array of option values data with the following structure:[['option_slug' => 'option_value','metadata' => 'metadata'],['option_slug' => 'option_value','metadata' => 'metadata']]

        return DB::transaction(function () use (
            $brokerId,
            $optionValuesData,
            $entity_id,
            $modelClass,
            $isAdmin,
            $zoneId,
        ) {
            
                $now = now();
                $updatesByCondition = [];
                $inserts = [];
                $options = BrokerOption::all()->pluck('id', 'slug');

                // Extract IDs that are different from 0 for comparison
                $idsToCompare = array_filter(
                    array_column($optionValuesData, 'id'),
                    function ($id) {
                        return ! empty($id) && $id != 0;
                    },
                );

                // Get existing option values for comparison

                $existingOptionValues = [];
                if (! empty($idsToCompare)) {
                    $existingOptionValues = OptionValue::whereIn(
                        'id',
                        $idsToCompare,
                    )
                        ->get()
                        ->keyBy('id');
                }
                // Result (Collection):,metadata is json endocded so ti is cast to array
                /*[
                   100 => OptionValue { id: 100, option_slug: 'logo', value: 'test.jpg', ... },
                   200 => OptionValue { id: 200, option_slug: 'name', value: 'Company A', ... },
                   300 => OptionValue { id: 300, option_slug: 'email', value: 'test@test.com', ... }
               ]*/

                //loop through received option values data
                foreach ($optionValuesData as $optionValueData) {
                    // Note: Don't JSON encode metadata here - the repository's bulkUpdate
                    // uses parameter binding and expects raw values. Laravel will handle
                    // the JSON casting based on the model's $casts property.

                    $optionValueData['updated_at'] = $now;
                    $id = $optionValueData['id'] ?? null;
                    $existingValue =
                        $id && isset($existingOptionValues[$id])
                        ? $existingOptionValues[$id]
                        : null;

                    //add new inserts only if id is null 
                    //beacuse the trading_name option is saved when brokers are created, the id is not null
                    //so when broker first save that category of options the request is of type PUT, not POST
                    //so all the options fromt thta category will have id not null except for the trading_name option
                    //so we need to add new inserts only if id is null

                  
                    if ($id === null) {
                        //these are new inserts
                        unset($optionValueData['id']);
                        $optionValueData['broker_id'] = $brokerId; // Ensure broker_id is set
                        $optionValueData['optionable_id'] = $entity_id;
                        $optionValueData['optionable_type'] = $modelClass;
                        $optionValueData['created_at'] = $now;
                        $optionValueData['broker_option_id'] = $options[$optionValueData['option_slug']];
                        $optionValueData['zone_id'] = $zoneId;
                        $optionValueData['metadata'] = $this->formatMetadata($optionValueData['metadata'], $isAdmin);
                        
                        
                        if ($isAdmin) {
                            $optionValueData['public_value'] = $optionValueData['value']; 
                        }

                        $inserts[] = $optionValueData;
                    } elseif ($id) {
                        // Check if this is an existing option value that needs comparison
                        if (! $isAdmin && $existingValue) {
                            $existingAdminMetadata = ($existingValue->metadata ?? [])['public_value'] ?? [];

                            //extract the broker value from metadata which is "value" key
                            //keep only broker metadata to compare with new value by using the hasValueChanged function
                            $existingValue->metadata = ($existingValue->metadata ?? [])['value'] ?? null;

                            //$optionValueData come from the clien with metadata  "metadata": {"unit": "eur"}
                            //and $existingValue->metadata is ["public_value"=>["unit"=>"eur"],"value"=>["unit"=>"eur"]]
                            //for the comparison, we need to keep only the broker metadata which is "value" key

                            // Compare values to determine if they've changed
                            $valueChanged = $this->hasValueChanged(
                                $existingValue,
                                $optionValueData,
                            );

                            if ($valueChanged) {
                                //get previous unit from metadata in case the option is a numberWithUnit
                                $previousUnit = $existingValue->metadata['unit'] ?? null;

                                // Set previous_value to old value and is_updated_entry to 1
                                $previousValue = $previousUnit
                                    ? ($existingValue->value ?? '') . '-' . $previousUnit
                                    : $existingValue->value ?? '';
                                $optionValueData['previous_value'] = $previousValue . '->' . ($existingValue->previous_value ?? '');

                                $optionValueData['is_updated_entry'] = 1;

                                //reconstruct metadata,add new value and existing admin metadata which has public key

                            } else {
                                // Keep existing previous_value and is_updated_entry
                                //to check if this are nedded for update
                                $optionValueData['previous_value'] = $existingValue->previous_value;

                                $optionValueData['is_updated_entry'] = $existingValue->is_updated_entry;
                            }

                            if (isset($optionValueData['metadata'])) {
                                $optionValueData['metadata'] = [
                                    'public_value' => $existingAdminMetadata,
                                    'value' => $optionValueData['metadata'],
                                ];
                            }
                        } elseif (
                            $isAdmin &&
                            isset($optionValueData['metadata'])
                        ) {
                            //get existing broker metadata and add to admin metadata
                            $existingMetadata = $existingValue->metadata??[];
                            $existingBrokerMetadata = $existingMetadata['value'] ?? [];
                                
                            $newAdminMetadata = $optionValueData['metadata'];

                            $optionValueData['metadata'] = [
                                'public_value' => $newAdminMetadata,
                                'value' => $existingBrokerMetadata,
                            ];
                        }

                        $id = $optionValueData['id'];
                        unset($optionValueData['id']);

                        //added now,not tested
                        if ($isAdmin) {
                            $optionValueData['public_value'] = $optionValueData['value'];
                            unset($optionValueData['value']);
                            $optionValueData['is_updated_entry'] = 0;
                        } else {
                            if (trim($existingValue['value']) !== trim($optionValueData['value'])) {
                                $optionValueData['is_updated_entry'] = 1;
                            }
                        }
                        //end added now,not tested
                        $updatesByCondition[$id] = $optionValueData;
                    }
                }

                // Bulk update
                if (! empty($updatesByCondition)) {
                    $this->repository->bulkUpdate(
                        $updatesByCondition,
                        $brokerId,
                    );
                }

                // Bulk insert
                if (! empty($inserts)) {
                    // JSON encode metadata for raw inserts (bulkCreate uses raw SQL insert)
                    // foreach ($inserts as &$insert) {
                    //     if (
                    //         isset($insert['metadata']) &&
                    //         is_array($insert['metadata'])
                    //     ) {
                    //         if ($isAdmin) {
                    //             // Admin-provided metadata goes under public_value
                    //             $insert['metadata'] = json_encode([
                    //                 'public_value' => $insert['metadata'],
                    //                 'value' => $insert['metadata'],
                    //             ]);
                    //         } else {
                    //             // Broker-provided metadata goes under value
                    //             $insert['metadata'] = json_encode([
                    //                 'value' => $insert['metadata'],
                    //             ]);
                    //         }
                    //     }
                    // }

                    //transform metadata to json
                    if (! empty($inserts)) {
                        foreach ($inserts as &$insert) {
                            if (is_array($insert['metadata'] ?? null)) {
                                $insert['metadata'] = json_encode($insert['metadata']);
                            }
                        }
                        unset($insert);
                    }
                    $this->repository->bulkCreate($inserts);
                }

                return true;
            
        });
    }

    public function deleteOptionValuesByOptionableId(
        int $optionableId,
        string $optionableType,
    ): bool {
        
        return OptionValue::query()
            ->where('optionable_id', $optionableId)
            ->where('optionable_type', $optionableType)
            ->delete();
    }

    /**
     * Delete option value
     */
    public function deleteOptionValue(int $id): bool
    {
        try {
            $optionValue = $this->repository->findByIdWithoutRelations($id);

            if (! $optionValue) {
                throw new \Exception('Option value not found');
            }

            return $this->repository->delete($optionValue);
        } catch (\Exception $e) {
            Log::error(
                'OptionValueService deleteOptionValue error: ' .
                    $e->getMessage(),
            );
            throw $e;
        }
    }

    public function validateEntityTypeAndId(
        string $entityType,
        int $entityId,
        int $brokerId,
        bool $isAdmin,
    ): bool {
        $allowedEntityTypes = [
            'broker',
            'account-type',
            'contest',
            'promotion',
            'company',
            'evaluation-step',
        ];
        $modelClass = ModelHelper::getModelClassFromSlug($entityType);

        $table = (new $modelClass())->getTable();
        if (! class_exists($modelClass)) {
            throw new \InvalidArgumentException(
                "Model class {$modelClass} not found",
            );
        }

        $rules = [
            'entity_type' => 'required|in:' . implode(',', $allowedEntityTypes),
            'entity_id' => 'required|exists:' . $table . ',id',
            'broker_id' => 'required|exists:brokers,id',
        ];

        $validator = Validator::make(
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'broker_id' => $brokerId,
            ],
            $rules,
        );
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        if (! $isAdmin && $entityType !== 'broker') {
            $belongs = $modelClass::query()
                ->whereKey($entityId)
                ->where('broker_id', $brokerId)
                ->exists();
            if (! $belongs) {
                throw new \InvalidArgumentException(
                    "Entity {$entityType} with id {$entityId} does not belong to broker {$brokerId}",
                );
            }
        }

        return true;
    }

   

    /**
     * Get option values by broker ID
     */
    public function getByBrokerId(
        int $brokerId,
    ): \Illuminate\Database\Eloquent\Collection {
        return $this->repository->getByBrokerId($brokerId);
    }

    /**
     * Get option values by broker option ID
     */
    public function getByBrokerOptionId(
        int $brokerOptionId,
    ): \Illuminate\Database\Eloquent\Collection {
        return $this->repository->getByBrokerOptionId($brokerOptionId);
    }



    /**
     * Search option values by value
     */
    public function searchByValue(
        string $search,
    ): \Illuminate\Database\Eloquent\Collection {
        return $this->repository->searchByValue($search);
    }

    /**
     * Check if option value has changed by comparing key fields
     */
    private function hasValueChanged(
        OptionValue $existingValue,
        array $newData,
    ): bool {
        // Compare the main value fields that indicate a change
        $fieldsToCompare = ['value', 'public_value', 'metadata'];

        foreach ($fieldsToCompare as $field) {
            if (isset($newData[$field])) {
                $existingFieldValue = $existingValue->$field;
                $newFieldValue = $newData[$field];

                // Handle different data types
                // Note: $existingValue->metadata is already cast to array by the model
                if (is_array($existingFieldValue) && is_array($newFieldValue)) {
                    if (
                        array_diff_assoc(
                            $existingFieldValue,
                            $newFieldValue,
                        ) !== [] ||
                        array_diff_assoc(
                            $newFieldValue,
                            $existingFieldValue,
                        ) !== []
                    ) {
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
