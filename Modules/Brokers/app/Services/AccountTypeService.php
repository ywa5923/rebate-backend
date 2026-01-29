<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\AccountTypeRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Repositories\MatrixHeaderRepository;
use Modules\Brokers\DTOs\AccountTypeFilters;
class AccountTypeService
{
    protected AccountTypeRepository $repository;
    protected MatrixHeaderRepository $matrixHeaderRepository;
    public function __construct(AccountTypeRepository $repository,MatrixHeaderRepository $matrixHeaderRepository)
    {
        $this->repository = $repository;
        $this->matrixHeaderRepository = $matrixHeaderRepository;
    }

    /**
     * Get paginated account types with filters
     */
    public function getAccountTypes(AccountTypeFilters $filters, int $broker_id): array
    {
        try {
            $accountTypes = $this->repository->getAccountTypes($filters, $broker_id);

            $response = [
                'success' => true,
                'data' => $accountTypes,
            ];

            if ($filters->base->perPage || $filters->base->page) {
                $response['pagination'] = [
                    'current_page' => $accountTypes->currentPage(),
                    'last_page' => $accountTypes->lastPage(),
                    'per_page' => $accountTypes->perPage(),
                    'total' => $accountTypes->total(),
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('AccountTypeService getPaginatedAccountTypes error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get account type by ID
     */
    public function getAccountTypeById(int $id): ?AccountType
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new account type with URLs
     */
    public function createAccountType(array $data): AccountType
    {
        return DB::transaction(function () use ($data) {
            try {
                // Extract URLs from data
                $urls = $data['urls'] ?? [];
                unset($data['urls']);

                // Create account type
                $accountType = $this->repository->create($data);

                // Create URLs if provided
                if (!empty($urls)) {
                    $this->repository->createUrls($accountType, $urls);
                }

                return $accountType->load(['broker', 'zone', 'urls']);

            } catch (\Exception $e) {
                Log::error('AccountTypeService createAccountType error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update account type with URLs
     */
    public function updateAccountType(int $id, array $data): AccountType
    {
      
        return DB::transaction(function () use ($id, $data) {
        try {
            $accountType = $this->repository->findByIdWithoutRelations($id);
            
            if (!$accountType) {
                throw new \Exception('Account type not found');
            }

            // Extract URLs and URLs to delete from data
            $urls = $data['urls'] ?? [];
            $urlsToDelete = $data['urls_to_delete'] ?? [];
            unset($data['urls'], $data['urls_to_delete']);

            // Update account type
            $this->repository->update($accountType, $data);

            // Handle URLs
            $this->repository->handleUrlUpdates($accountType, $urls, $urlsToDelete);

            return $accountType->load(['broker', 'zone', 'urls']);

        } catch (\Exception $e) {
            Log::error('AccountTypeService updateAccountType error: ' . $e->getMessage());
                throw $e;
            }
        });
    }


    public function deleteMatrixHeader(int $accountTypeId,$broker_id): bool
    {
        $accountTypeName=$this->repository->getAccountTypeName($accountTypeId);
        
        if($accountTypeName){
        $matrixHeader=$this->matrixHeaderRepository->getColumnMatrixHeaderByTitle($accountTypeName,$broker_id);
      
        if($matrixHeader && $matrixHeader->id){
            return $this->matrixHeaderRepository->deleteById($matrixHeader->id);
            }
        }
        return false;
    }
    /**
     * Delete account type
     */
    public function deleteAccountType(int $id,$broker_id): bool
    {
        
        try {
            $accountType = $this->repository->findByIdWithoutRelations($id);

            if($accountType->broker_id!=$broker_id){
                throw new \Exception('You are not authorized to delete this account type');
            }
            
            if (!$accountType) {
                throw new \Exception('Account type not found');
            }

            DB::beginTransaction();
            $this->repository->delete($accountType);
            $this->repository->deleteAccountTypeUrls($accountType,$broker_id);
            DB::commit();
            return true;
            

        } catch (\Exception $e) {
            Log::error('AccountTypeService deleteAccountType error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate account type data
     */
    public function validateAccountTypeData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'broker_type' => $isUpdate ? 'sometimes|required|in:broker,crypto,prop_firm' : 'required|in:broker,crypto,prop_firm',
            'commission_value' => 'nullable|numeric|min:0',
            'commission_value_p' => 'nullable|numeric|min:0',
            'commission_unit' => 'nullable|string|max:50',
            'commission_unit_p' => 'nullable|string|max:50',
            'execution_model' => 'nullable|string|max:100',
            'execution_model_p' => 'nullable|string|max:100',
            'max_leverage' => 'nullable|string|max:50',
            'max_leverage_p' => 'nullable|string|max:50',
            'spread_type' => 'nullable|string|max:100',
            'spread_type_p' => 'nullable|string|max:100',
            'min_deposit_value' => 'nullable|string|max:50',
            'min_deposit_unit' => 'nullable|string|max:50',
            'min_deposit_value_p' => 'nullable|string|max:50',
            'min_deposit_unit_p' => 'nullable|string|max:50',
            'min_trade_size_value' => 'nullable|string|max:50',
            'min_trade_size_unit' => 'nullable|string|max:50',
            'min_trade_size_value_p' => 'nullable|string|max:50',
            'min_trade_size_unit_p' => 'nullable|string|max:50',
            'stopout_level_value' => 'nullable|string|max:50',
            'stopout_level_unit' => 'nullable|string|max:50',
            'stopout_level_value_p' => 'nullable|string|max:50',
            'stopout_level_unit_p' => 'nullable|string|max:50',
            'trailing_stops' => 'boolean',
            'trailing_stops_p' => 'boolean',
            'allow_scalping' => 'boolean',
            'allow_scalping_p' => 'boolean',
            'allow_hedging' => 'boolean',
            'allow_hedging_p' => 'boolean',
            'allow_news_trading' => 'boolean',
            'allow_news_trading_p' => 'boolean',
            'allow_cent_accounts' => 'boolean',
            'allow_cent_accounts_p' => 'boolean',
            'allow_islamic_accounts' => 'boolean',
            'allow_islamic_accounts_p' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
            'is_invariant' => 'nullable|boolean',
            'broker_id' => $isUpdate ? 'sometimes|required|exists:brokers,id' : 'required|exists:brokers,id',
            'zone_id' => 'nullable|exists:zones,id',
            // URL validation
            'urls' => 'nullable|array',
            'urls.*.id' => 'nullable|exists:urls,id',
            'urls.*.url' => 'required_with:urls|url',
            'urls.*.url_p' => 'nullable|url',
            'urls.*.url_type' => 'required_with:urls|string|max:100',
            'urls.*.name' => 'required_with:urls|string|max:500',
            'urls.*.name_p' => 'nullable|string|max:500',
            'urls.*.slug' => 'required_with:urls|string|max:500',
            'urls.*.description' => 'nullable|string|max:500',
            'urls.*.category_position' => 'nullable|integer|min:0',
            'urls.*.option_category_id' => 'required_with:urls|exists:option_categories,id',
            'urls.*.zone_id' => 'nullable|exists:zones,id',
            'urls.*.is_invariant' => 'nullable|boolean',
            'urls_to_delete' => 'nullable|array',
            'urls_to_delete.*' => 'exists:urls,id',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \Exception('Validation failed: ' . json_encode($validator->errors()));
        }

        return $validator->validated();
    }

    /**
     * Get form data for creating/editing
     */
    public function getFormData(): array
    {
        return [
            'broker_types' => ['broker', 'crypto', 'prop_firm'],
            'url_types' => ['mobile', 'webplatform', 'swap', 'commission', 'other']
        ];
    }

    /**
     * Get account types by broker ID
     */
    public function getByBrokerId(int $brokerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByBrokerId($brokerId);
    }

    /**
     * Get account types by broker type
     */
    public function getByBrokerType(string $brokerType): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByBrokerType($brokerType);
    }

    /**
     * Get active account types
     */
    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Search account types by name
     */
    public function searchByName(string $search): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->searchByName($search);
    }
} 