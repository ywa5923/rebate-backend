<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
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
    public function getRegulatorsByCompany(int $company_id, string $language_code, ?int $zone_id): Collection
    {
        return $this->model->newQuery()
            ->whereHas('companies', fn ($query) => $query->where('companies.id', $company_id))
            ->with([
                'translations' => fn ($query) => $query->where('language_code', $language_code),
            ])
            ->when(
                $zone_id,
                fn ($query) => $query->where(function ($q) use ($zone_id) {
                    $q->where('zone_id', $zone_id)->orWhere('is_invariant', true);
                }),
                fn ($query) => $query->where('is_invariant', true)->whereNull('zone_id'),
            )
            ->get();
    }

}
