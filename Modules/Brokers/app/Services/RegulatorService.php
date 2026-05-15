<?php

namespace Modules\Brokers\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\Regulator;
use Modules\Brokers\Repositories\RegulatorRepository;

class RegulatorService
{
    public function __construct(
        protected RegulatorRepository $repository,
    ) {
    }

    /**
     * @return Collection<int, \Modules\Brokers\Models\Regulator>
     */
    public function getRegulatorsList(string $language_code, ?int $zone_id): Collection
    {
        return $this->repository->getRegulatorsList($language_code, $zone_id);
    }

    public function attachRegulatorToCompany(int $regulator_id, int $company_id, ?int $zone_id = null): Regulator
    {
        return $this->repository->attachRegulatorToCompany($regulator_id, $company_id, $zone_id);
    }

    public function detachRegulatorFromCompany(int $regulator_id, int $company_id, ?int $zone_id = null): void
    {
        $this->repository->detachRegulatorFromCompany($regulator_id, $company_id, $zone_id);
    }
}
