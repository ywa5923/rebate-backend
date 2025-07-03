<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\RegulatorRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\Regulator;

class RegulatorService
{
    protected RegulatorRepository $repository;

    public function __construct(RegulatorRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated regulators with filters
     */
    public function getRegulators(Request $request): array
    {
        try {
            $regulators = $this->repository->getRegulators($request);

            $response = [
                'success' => true,
                'data' => $regulators,
            ];

            if ($request->has('per_page')) {
                $response['pagination'] = [
                    'current_page' => $regulators->currentPage(),
                    'last_page' => $regulators->lastPage(),
                    'per_page' => $regulators->perPage(),
                    'total' => $regulators->total(),
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('RegulatorService getRegulators error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get regulator by ID
     */
    public function getRegulatorById(int $id): ?Regulator
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new regulator
     */
    public function createRegulator(array $data): Regulator
    {
        return DB::transaction(function () use ($data) {
            try {
                $regulator = $this->repository->create($data);
                return $regulator->load(['brokers', 'translations']);

            } catch (\Exception $e) {
                Log::error('RegulatorService createRegulator error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update regulator
     */
    public function updateRegulator(int $id, array $data): Regulator
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $regulator = $this->repository->findByIdWithoutRelations($id);
                
                if (!$regulator) {
                    throw new \Exception('Regulator not found');
                }

                $this->repository->update($regulator, $data);

                return $regulator->load(['brokers', 'translations']);

            } catch (\Exception $e) {
                Log::error('RegulatorService updateRegulator error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete regulator
     */
    public function deleteRegulator(int $id): bool
    {
        try {
            $regulator = $this->repository->findByIdWithoutRelations($id);
            
            if (!$regulator) {
                throw new \Exception('Regulator not found');
            }

            return $this->repository->delete($regulator);

        } catch (\Exception $e) {
            Log::error('RegulatorService deleteRegulator error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate regulator data
     */
    public function validateRegulatorData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'abreviation' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:255',
            'country_p' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_p' => 'nullable|string',
            'rating' => 'nullable|numeric|min:0|max:5',
            'rating_p' => 'nullable|numeric|min:0|max:5',
            'capitalization' => 'nullable|string|max:1000',
            'capitalization_p' => 'nullable|string|max:1000',
            'segregated_clients_money' => 'nullable|string|max:255',
            'segregated_clients_money_p' => 'nullable|string|max:255',
            'deposit_compensation_scheme' => 'nullable|string|max:255',
            'deposit_compensation_scheme_p' => 'nullable|string|max:255',
            'negative_balance_protection' => 'nullable|string|max:255',
            'negative_balance_protection_p' => 'nullable|string|max:255',
            'rebates' => 'nullable|boolean',
            'rebates_p' => 'nullable|boolean',
            'enforced' => 'nullable|boolean',
            'enforced_p' => 'nullable|boolean',
            'max_leverage' => 'nullable|string',
            'max_leverage_p' => 'nullable|string',
            'website' => 'nullable|url|max:500',
            'website_p' => 'nullable|url|max:500',
            'status' => 'nullable|in:published,pending,rejected',
            'status_reason' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * Get form data for creating/editing regulator
     */
    public function getFormData(): array
    {
        return [
            'statuses' => ['published', 'pending', 'rejected'],
            'countries' => $this->getUniqueCountries(),
        ];
    }

    /**
     * Get unique countries from existing regulators
     */
    private function getUniqueCountries(): array
    {
        return $this->repository->getActive()
            ->pluck('country')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get regulators by country
     */
    public function getByCountry(string $country): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByCountry($country);
    }

    /**
     * Get active regulators
     */
    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Search regulators by name
     */
    public function searchByName(string $search): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->searchByName($search);
    }

    /**
     * Get regulators by status
     */
    public function getByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByStatus($status);
    }
} 