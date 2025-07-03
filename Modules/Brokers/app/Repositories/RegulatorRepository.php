<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Models\Regulator;
use Modules\Brokers\Transformers\RegualtorCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RegulatorRepository
{
    protected Regulator $model;

    public function __construct(Regulator $model)
    {
        $this->model = $model;
    }

    // EXISTING METHODS
    public function getUniqueList(array $langaugeCondition)
    {
        $results=[];
        $this->getRegulatorsWithTranslationsQB($langaugeCondition)->chunk(100,function ($regulators) use (&$results){
          $list=[];
         $regulatorCollection=new RegualtorCollection($regulators);
          foreach($regulatorCollection->resolve() as $regulator)
          {
            //array_push($list, [$regulator["abreviation"]=>$regulator["abreviation"]."-".$regulator["country"]]);
            $list[trim($regulator["abreviation"])]=$regulator["abreviation"]."-".$regulator["country"];
          //  $key=$regulator["abreviation"]."-".$regulator["country"];
          //   $list[$key]=trim($regulator["abreviation"]);
          }

          $results=array_merge($results,$list);
        });
         
        return $results;
    }
    public function getRegulatorsWithTranslationsQB($langaugeCondition)
    {
        return Regulator::with(["translations"=>function (Builder $query)use ($langaugeCondition){
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
             $query->where(...$langaugeCondition);
         }]);
    }

    // NEW METHODS FOR CRUD API
    /**
     * Get paginated regulators with filters
     */
    public function getRegulators(Request $request): LengthAwarePaginator|Collection
    {
        if($request->has('language_code')){
            $locale = $request->language_code;
        }else{
            $locale = 'en';
        }
        
        $query = $this->model->with(['brokers', 'translations' => function($query) use ($locale) {
            $query->where('language_code', $locale);
        }]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        if($request->has('per_page') || $request->has('page')){
             // Paginate with specific page
             $perPage = $request->get('per_page', 15);
             $page = $request->get('page', 1);
            return $query->paginate($perPage, ['*'], 'page', $page);
        }else{
            return $query->get();
        }
    }

    /**
     * Get regulator by ID with relations
     */
    public function findById(int $id): ?Regulator
    {
        return $this->model->with(['brokers', 'translations'])->find($id);
    }

    /**
     * Get regulator by ID without relations
     */
    public function findByIdWithoutRelations(int $id): ?Regulator
    {
        return $this->model->find($id);
    }

    /**
     * Create new regulator
     */
    public function create(array $data): Regulator
    {
        return $this->model->create($data);
    }

    /**
     * Update regulator
     */
    public function update(Regulator $regulator, array $data): bool
    {
        return $regulator->update($data);
    }

    /**
     * Delete regulator
     */
    public function delete(Regulator $regulator): bool
    {
        return $regulator->delete();
    }

    /**
     * Get regulators by country
     */
    public function getByCountry(string $country): Collection
    {
        return $this->model->where('country', $country)->get();
    }

    /**
     * Get active regulators
     */
    public function getActive(): Collection
    {
        return $this->model->where('status', 'published')->get();
    }

    /**
     * Search regulators by name
     */
    public function searchByName(string $search): Collection
    {
        return $this->model->where('name', 'like', "%{$search}%")
                          ->orWhere('abreviation', 'like', "%{$search}%")
                          ->get();
    }

    /**
     * Get regulators by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('enforced')) {
            $query->where('enforced', $request->boolean('enforced'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('abreviation', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (in_array($sortBy, ['name', 'country', 'rating', 'status', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }
}