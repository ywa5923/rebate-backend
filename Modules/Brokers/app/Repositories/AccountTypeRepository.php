<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brokers\DTOs\AccountTypeFilters;
use Modules\Brokers\Enums\UrlTypeEnum;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Models\OptionValue;
use Modules\Brokers\Models\Url;

class AccountTypeRepository
{
    protected AccountType $model;

    public function __construct(AccountType $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated account types with filters
     */
    public function getFilteredAccountTypes(AccountTypeFilters $filters, int $broker_id): LengthAwarePaginator|Collection
    {
        $query = $this->model->newQuery();
        $query->where('broker_id', $broker_id);
        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $this->applySorting($query, $filters);

        if ($filters->base->perPage || $filters->base->page) {
            // Paginate with specific page
            $perPage = $filters->base->perPage;
            $page = $filters->base->page;

            return $query->paginate($perPage, ['*'], 'page', $page);
        } else {
            return $query->get();
        }
    }

    /**
     * Get account type by ID with relations
     */
    public function findById(int $id): ?AccountType
    {
        return $this->model->with(['broker', 'zone', 'translations', 'urls', 'optionValues'])->find($id);
    }

    /**
     * Get account type by ID without relations
     */
    public function findByIdWithoutRelations(int $id): ?AccountType
    {
        return $this->model->find($id);
    }

    /**
     * Create new account type
     */
    public function create(array $data): AccountType
    {
        return $this->model->create($data);
    }

    /**
     * Update account type
     */
    public function update(AccountType $accountType, array $data): bool
    {
        return $accountType->update($data);
    }

    /**
     * Delete account type
     */
    public function delete(AccountType $accountType): bool
    {
        return $accountType->delete();
    }

    /**
     * Delete URLs for account type
     */
    public function deleteAccountTypeUrls(AccountType $accountType, int $broker_id): bool
    {
        return Url::where('urlable_type', AccountType::class)
            ->where('broker_id', $broker_id)
            ->delete();
    }

    
    /**
     * Get account types by broker ID
     */
    public function getByBrokerId(int $brokerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('broker_id', $brokerId)->get();
    }

   

    /**
     * Apply filters to query
     */
    private function applyFilters(Builder $query, AccountTypeFilters $filters): void
    {

        if ($filters->accountTypeId) {
            $query->where('id', $filters->accountTypeId);
        }

        $withArray = [];

        if ($filters->base->zoneCode) {
            $withArray['optionValues'] = function ($q) use ($filters) {
                $q->where(function ($subQ) use ($filters) {
                    $subQ->where('is_invariant', 1)
                        ->orWhere('zone_code', $filters->base->zoneCode);
                });
            };
        } else {
            //if zone_code is not provided, we need to get the option values
            //for the account type that have no zone_code and zone_id, i.e original data submitted by broker
            $withArray['optionValues'] = function ($q) {
                $q->where(function ($subQ) {
                    $subQ->where('zone_code', null)->where('zone_id', null);
                });
            };
        }

        if ($filters->base->languageCode) {
            if (isset($withArray['optionValues'])) {
                $withArray['optionValues.translations'] = function ($q) use ($filters) {
                    $q->where('language_code', $filters->base->languageCode);
                };
            } else {
                $withArray['optionValues.translations'] = function ($q) use ($filters) {
                    $q->where('language_code', $filters->base->languageCode);
                };
            }
        }

        if (! empty($withArray)) {
            $query->with($withArray);
        } else {
            $query->with(['broker', 'urls', 'optionValues']);
        }

    }

    /**
     * Apply sorting to query
     */
    private function applySorting(Builder $query, AccountTypeFilters $filters): void
    {
        $sortBy = $filters->sortBy ?? 'created_at';
        $sortDirection = $filters->sortDirection ?? 'asc';
        $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * Get account type name by account type ID
     */
    public function getAccountTypeName(int $accountTypeId): string
    {
        return $this->model->where('id', $accountTypeId)->with('optionValues', function ($q) {
            $q->where('option_slug', 'account_type_name');
        })->first()->optionValues->first()->value;
    }

    /**
     * Get account types with platform links
     */
    public function getAccountTypesWithPlatformLinks(int $broker_id, string $lang, ?string $zone = null): Collection
    {
        $query = $this->model->newQuery();

        return $query->where('broker_id', $broker_id)
            ->with([
                // affiliate URLs + their translations in chosen language
                'urls' => fn ($q) => $q
                    ->whereIn('url_type', [
                        UrlTypeEnum::TRADING_PLATFORM->value,
                    ])
                    ->where(function ($q) use ($zone) {
                        if ($zone === null) {
                            $q->whereNull('zone_id');
                        } else {
                            $q->where('zone_id', $zone);
                        }
                    })
                    ->orderBy('url_type', 'asc')
                    ->with(['translations' => fn ($t) => $t->where('language_code', $lang)]),

                // option_values where option.slug = 'account_name' and zone_code matches (or is null)
                'optionValues' => fn ($q) => $q
                    ->where(function ($op) use ($zone) {
                        if ($zone === null) {
                            $op->whereNull('zone_code');
                        } else {
                            $op->where('zone_code', $zone);
                        }
                    })
                    ->where('option_slug', 'account_type_name')
                    ->with(['translations' => fn ($t) => $t->where('language_code', $lang)]),
            ])
            ->orderBy(
                OptionValue::select('value')
                    ->whereColumn('option_values.optionable_id', 'account_types.id')
                    ->where('option_values.optionable_type', AccountType::class)
                    ->where('option_slug', 'account_type_name')
                    ->when(
                        $zone === null,
                        fn ($q) => $q->whereNull('zone_code'),
                        fn ($q) => $q->where('zone_code', $zone)
                    )
                    ->limit(1),
                'asc'
            )
            ->get();
    }

    /**
     * Find account type by ID
     */
    public function find(int $id): ?AccountType
    {
        return $this->model->find($id);
    }

    /**
     * Find account type by ID and broker ID
     */
    public function findByIdAndBrokerId(int $id, int $broker_id): ?AccountType
    {
        return $this->model->where('id', $id)->where('broker_id', $broker_id)->first();
    }

    /**
     * Find account type by ID with option values
     */
    public function findByIdWithOptionValues(int $id): ?AccountType
    {
        return $this->model->where('id', $id)->with('optionValues')->first();
    }
}
