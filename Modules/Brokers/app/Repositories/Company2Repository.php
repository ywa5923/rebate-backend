<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Models\Company;
use Modules\Brokers\Transformers\CompanyCollection;
use Modules\Brokers\Repositories\CompanyUniqueListInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Company2Repository
{
    protected Company $model;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }

    public function getUniqueList(array $langaugeCondition, string $fieldName): array
    {
        $results = [];
        $this->getCompaniesWithTranslationsQB($langaugeCondition)->chunk(100, function ($companies) use (&$results, $fieldName) {
            $list = [];
            $companyCollection = new CompanyCollection($companies);
            foreach ($companyCollection->resolve() as $company) {
                $items = explode(",", $company[$fieldName]);
                foreach ($items as $item) {
                    if (!array_key_exists(trim($item), $list) && trim($item) !== "")
                        $list[trim($item)] = trim($item);
                }
            }

            $results = array_merge($results, $list);
        });
        return array_unique($results);
    }

    public function getCompaniesWithTranslationsQB($langaugeCondition)
    {
        return Company::with(["translations" => function (Builder $query) use ($langaugeCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where(...$langaugeCondition);
        }]);
    }

    // ===== NEW ADDED METHODS FOR CRUD OPERATIONS =====

    /**
     * Get paginated companies with filters
     */
    public function getCompanies(Request $request): LengthAwarePaginator|Collection
    {

        //tested with http://localhost:8080/api/v1/companies?language_code=ro&broker_id=200&company_id=1&zone_code=sua
        //DB::enableQueryLog();
        //dd(DB::getQueryLog());

        $query = $this->model->newQuery();
      
       
        $this->applyFilters($query, $request);
       

        if ($request->has('per_page') || $request->has('page')) {
            // Paginate with specific page
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);
            return $query->paginate($perPage, ['*'], 'page', $page);
        } else {
            return $query->get();
        }
    }
    

    /**
     * Get company by ID with relations
     */
    public function findById(int $id): ?Company
    {
        return $this->model->with(['broker', 'zone', 'translations'])->find($id);
    }

    /**
     * Get company by ID without relations
     */
    public function findByIdWithoutRelations(int $id): ?Company
    {
        return $this->model->find($id);
    }

    /**
     * Create new company
     */
    public function create(array $data): Company
    {
        return $this->model->create($data);
    }

    /**
     * Update company
     */
    public function update(Company $company, array $data): bool
    {
        return $company->update($data);
    }

    /**
     * Delete company
     */
    public function delete(Company $company): bool
    {
        return $company->delete();
    }

    /**
     * Get brokers for form dropdown
     */
    public function getBrokersForForm(): Collection
    {
        return DB::table('brokers')->select('id', 'registration_language as name')->get();
    }

    /**
     * Get zones for form dropdown
     */
    public function getZonesForForm(): Collection
    {
        return DB::table('zones')->select('id', 'name')->get();
    }

    /**
     * Get companies by broker ID
     */
    public function getByBrokerId(int $brokerId): Collection
    {
        return $this->model->where('broker_id', $brokerId)->get();
    }

    /**
     * Get companies by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Search companies by name
     */
    public function searchByName(string $search): Collection
    {
        return $this->model->where('name', 'like', "%{$search}%")->get();
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {


        if ($request->has('broker_id')) {
            $query->where('broker_id', $request->broker_id);
        }

        if ($request->has('company_id')) {
            $query->where('id', $request->company_id);
        }

      
        if ($request->has('zone_code')) {
            $withArray = ['optionValues' => function ($q) use ($request) {

                $q->where(function ($subQ) use ($request) {
                    $subQ->where('is_invariant', 1)
                        ->orWhere('zone_code', $request->zone_code);
                });
                
            }];
        }


        if ($request->has('language_code')) {
           
            $withArray['optionValues.translations'] = function ($q) use ($request) {
                $q->where('language_code', $request->language_code);
            };
        }
        if(!empty($withArray)){
            $query->with($withArray);
        }else{
            $query->with('optionValues');
            
        }
       
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortFields = ['name', 'status', 'year_founded', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }
}
