<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\DTOs\AccountTypeFilters;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Repositories\AccountTypeRepository;
use Modules\Brokers\Repositories\MatrixHeaderRepository;
use Modules\Brokers\Transformers\AccountTypeResource;

class AccountTypeService
{
    protected AccountTypeRepository $repository;

    protected MatrixHeaderRepository $matrixHeaderRepository;

    public function __construct(AccountTypeRepository $repository, MatrixHeaderRepository $matrixHeaderRepository)
    {
        $this->repository = $repository;
        $this->matrixHeaderRepository = $matrixHeaderRepository;
    }

    /**
     * Get paginated account types with filters
     */
    public function getFilteredAccountTypes(AccountTypeFilters $filters, int $broker_id): array
    {
        
            $accountTypes = $this->repository->getFilteredAccountTypes($filters, $broker_id);

            $response = [
                'success' => true,
                'data' => AccountTypeResource::collection($accountTypes),
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
        
    }

    /**
     * Get account type by ID
     */
    public function getAccountTypeById(int $id): ?AccountType
    {
        return $this->repository->findById($id);
    }

   

    public function deleteMatrixHeader(int $accountTypeId, int $broker_id): bool
    {
        $accountTypeName = $this->repository->getAccountTypeName($accountTypeId);

        if ($accountTypeName) {
            $matrixHeader = $this->matrixHeaderRepository->getColumnMatrixHeaderByTitle($accountTypeName, $broker_id);

            if ($matrixHeader && $matrixHeader->id) {
                return $this->matrixHeaderRepository->deleteById($matrixHeader->id);
            }
        }

        return false;
    }

    /**
     * Delete account type
     */
    public function deleteAccountType(int $id, int $broker_id): AccountType
    {
        //$accountType = $this->repository->findByIdWithoutRelations($id);
        $accountType = $this->repository->findByIdWithOptionValues($id);

        if (! $accountType) {
            throw new \Exception('Account type not found');
        }

        if ($accountType->broker_id !== $broker_id) {
            throw new \Exception('You are not authorized to delete this account type');
        }

        $this->repository->delete($accountType);
        return $accountType;
    }

    
}
