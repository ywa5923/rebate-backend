<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\Company;
use Modules\Brokers\Models\Regulator;

class RegulatorRepository
{
    protected Regulator $model;

    public function __construct(Regulator $model)
    {
        $this->model = $model;
    }

    /**
     * @return Collection<int, Regulator>
     */
    public function getRegulatorsList(string $language_code, ?int $zone_id): Collection
    {
        return $this->model->newQuery()
            ->with([
                'translations' => fn ($query) => $query->where('language_code', $language_code),
            ])
            ->when(
                $zone_id,
                fn ($query) => $query->where(function ($q) use ($zone_id) {
                    $q->where('zone_id', $zone_id)->orWhere('is_invariant', true);
                }),
                fn ($query) => $query->where('is_invariant', true)->whereNull('zone_id'),
            )->get();
    }

    public function attachRegulatorToCompany(int $regulator_id, int $company_id, ?int $zone_id = null): Regulator
    {
        $company = Company::query()->findOrFail($company_id);

        $company->regulators()->syncWithoutDetaching([
            $regulator_id => ['zone_id' => $zone_id],
        ]);

        return $this->model->newQuery()->findOrFail($regulator_id);
    }

    public function detachRegulatorFromCompany(int $regulator_id, int $company_id, ?int $zone_id = null):?Regulator
    {
        $company = Company::query()->findOrFail($company_id);

        $relation = $company->regulators();

        if ($zone_id === null) {
            $relation->wherePivotNull('zone_id');
        } else {
            $relation->wherePivot('zone_id', $zone_id);
        }

        $detached= $relation->detach($regulator_id);

        return $detached===0 ? null : $this->model->newQuery()->findOrFail($regulator_id);
       
    }
}
