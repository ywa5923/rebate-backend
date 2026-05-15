<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\Company;

class ComanyRepository
{
    protected Company $model;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }

    /**
     * Get company by ID without relations
     */
    public function findByIdWithoutRelations(int $id): ?Company
    {
        return $this->model->find($id);
    }

    public function findBrokerCompanies(int $broker_id, string $language_code, ?int $zone_id): Collection
    {
        return $this->model->where('broker_id', $broker_id)->with(['optionValues' => function ($query) use ($zone_id) {
            if ($zone_id) {
                $query->where(function ($q) use ($zone_id) {
                    $q->where('zone_id', $zone_id)->orWhere('is_invariant', true);
                });
            } else {
                $query->where('is_invariant', 1)->where('zone_code', null)->where('zone_id', null);
            }
        }, 'optionValues.translations' => function ($query) use ($language_code) {
            $query->where('language_code', $language_code);
        },
            'regulators' => function ($query) use ($zone_id) {
                $query->when(
                    $zone_id,
                    fn ($query) => $query->where(function ($q) use ($zone_id) {
                        $q->where('regulators.zone_id', $zone_id)
                            ->orWhere('regulators.is_invariant', true);
                    }),
                    fn ($query) => $query->where('regulators.is_invariant', true)
                        ->whereNull('regulators.zone_id'),
                );
            },
            'regulators.translations' => function ($query) use ($language_code) {
                $query->where('language_code', $language_code);
            },
        ])->get();
    }

    public function deleteCompany(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    public function findCompanyWithRegulators(int $company_id, string $language_code, ?int $zone_id): Collection
    {
        return $this->model->where('id', $company_id)->with([
            'regulators' => function ($query) use ($zone_id) {
                $query->when(
                    $zone_id,
                    fn ($query) => $query->where(function ($q) use ($zone_id) {
                        $q->where('regulators.zone_id', $zone_id)
                            ->orWhere('regulators.is_invariant', true);
                    }),
                    fn ($query) => $query->where('regulators.is_invariant', true)
                        ->whereNull('regulators.zone_id'),
                );
            },
            'regulators.translations' => function ($query) use ($language_code) {
                $query->where('language_code', $language_code);
            },
            'optionValues' => function ($query) use ($zone_id) {
                if ($zone_id) {
                    $query->where('zone_id', $zone_id)->orWhere('is_invariant', 1);
                } else {
                    $query->where('is_invariant', 1)->where('zone_code', null)->where('zone_id', null);
                }
            },
            'optionValues.translations' => function ($query) use ($language_code) {
                $query->where('language_code', $language_code);
            },
        ])->get();
    }
}
