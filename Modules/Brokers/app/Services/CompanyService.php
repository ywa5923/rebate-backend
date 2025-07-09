<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\CompanyRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\Company;

class CompanyService
{
    protected CompanyRepository $repository;

    public function __construct(CompanyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated companies with filters
     */
    public function getCompanies(Request $request): array
    {
        try {
            $companies = $this->repository->getCompanies($request);

            $response = [
                'success' => true,
                'data' => $companies,
            ];

            if ($request->has('per_page')) {
                $response['pagination'] = [
                    'current_page' => $companies->currentPage(),
                    'last_page' => $companies->lastPage(),
                    'per_page' => $companies->perPage(),
                    'total' => $companies->total(),
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('CompanyService getCompanies error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get company by ID
     */
    public function getCompanyById(int $id): ?Company
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new company
     */
    public function createCompany(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            try {
                // Create company
                $company = $this->repository->create($data);

                return $company->load(['broker', 'zone', 'translations']);

            } catch (\Exception $e) {
                Log::error('CompanyService createCompany error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update company
     */
    public function updateCompany(int $id, array $data): Company
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $company = $this->repository->findByIdWithoutRelations($id);
                
                if (!$company) {
                    throw new \Exception('Company not found');
                }

                // Update company
                $this->repository->update($company, $data);

                return $company->load(['broker', 'zone', 'translations']);

            } catch (\Exception $e) {
                Log::error('CompanyService updateCompany error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete company
     */
    public function deleteCompany(int $id): bool
    {
        try {
            $company = $this->repository->findByIdWithoutRelations($id);
            
            if (!$company) {
                throw new \Exception('Company not found');
            }

            return $this->repository->delete($company);

        } catch (\Exception $e) {
            Log::error('CompanyService deleteCompany error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate company data
     */
    public function validateCompanyData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'name' => $isUpdate ? 'sometimes|required|string|max:250' : 'required|string|max:250',
            'name_p' => 'nullable|string|max:250',
            'licence_number' => 'nullable|string|max:10000',
            'licence_number_p' => 'nullable|string|max:10000',
            'banner' => 'nullable|string|max:10000',
            'banner_p' => 'nullable|string|max:10000',
            'description' => 'nullable|string|max:10000',
            'description_p' => 'nullable|string|max:10000',
            'crypto_categories' => 'nullable|string|max:10000',
            'crypto_categories_p' => 'nullable|string|max:10000',
            'year_founded' => 'nullable|string|max:10000',
            'year_founded_p' => 'nullable|string|max:10000',
            'employees' => 'nullable|string|max:10000',
            'employees_p' => 'nullable|string|max:10000',
            'headquarters' => 'nullable|string|max:10000',
            'headquarters_p' => 'nullable|string|max:10000',
            'offices' => 'nullable|string|max:10000',
            'offices_p' => 'nullable|string|max:10000',
            'status' => 'nullable|in:published,pending,rejected',
            'status_reason' => 'nullable|string|max:10000',
            'broker_id' => $isUpdate ? 'sometimes|required|exists:brokers,id' : 'required|exists:brokers,id',
            'zone_id' => 'nullable|exists:zones,id',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * Get form data for creating/editing companies
     */
    public function getFormData(): array
    {
        try {
            // Get brokers for dropdown
            $brokers = $this->repository->getBrokersForForm();
            
            // Get zones for dropdown
            $zones = $this->repository->getZonesForForm();

            return [
                'brokers' => $brokers,
                'zones' => $zones,
                'status_options' => [
                    'published' => 'Published',
                    'pending' => 'Pending',
                    'rejected' => 'Rejected'
                ]
            ];

        } catch (\Exception $e) {
            Log::error('CompanyService getFormData error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get companies by broker ID
     */
    public function getByBrokerId(int $brokerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByBrokerId($brokerId);
    }

    /**
     * Get companies by status
     */
    public function getByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByStatus($status);
    }

    /**
     * Search companies by name
     */
    public function searchByName(string $search): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->searchByName($search);
    }
} 