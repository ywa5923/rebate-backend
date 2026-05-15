<?php

namespace Modules\Brokers\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Brokers\Repositories\ComanyRepository;
use Modules\Brokers\Repositories\RegulatorRepository;

class CompanyService
{
    public function __construct(
        protected ComanyRepository $repository,
        protected RegulatorRepository $regulatorRepository,
        protected OptionValueService $optionValueService,
    ) {
    }

    /**
     * Get paginated companies with filters
     *
     * @throws \Exception
     */
    public function getCompanies(int $broker_id, string $language_code, ?int $zone_id): Collection
    {
        return $this->repository->findBrokerCompanies($broker_id, $language_code, $zone_id);
    }

    /**
     * Delete a company
     *
     * @throws \Exception
     */
    public function deleteCompany(int $id): bool
    {
        try {
            DB::beginTransaction();
            $company = $this->repository->findByIdWithoutRelations($id);

            if (! $company) {
                throw new \Exception('Company not found');
            }

            $this->repository->deleteCompany($id);
            //delete all option values for this company
            $this->optionValueService->deleteOptionValuesByOptionableId($id, 'company');
            DB::commit();

            return true;

        } catch (\Exception $e) {
            Log::error('CompanyService deleteCompany error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \Modules\Brokers\Models\Regulator>
     */
    public function getRegulators(int $company_id, string $language_code, ?int $zone_id): Collection
    {
        return $this->regulatorRepository->getRegulatorsByCompany($company_id, $language_code, $zone_id);
    }
}
