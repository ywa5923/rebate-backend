<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\AccountType;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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
    public function getAccountTypes(Request $request): LengthAwarePaginator|Collection
    {
        if($request->has('language_code')){
            $locale = $request->language_code;
        }else{
            $locale = 'en';
        }
        $query = $this->model->with(['broker', 'zone', 'urls.translations', 'translations' => function($query) use ($locale) {
            $query->where('language_code', $locale);
        }]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
       // $this->applySorting($query, $request);

       
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
     * Get account type by ID with relations
     */
    public function findById(int $id): ?AccountType
    {
        return $this->model->with(['broker', 'zone', 'translations', 'urls'])->find($id);
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
     * Create URLs for account type
     */
    public function createUrls(AccountType $accountType, array $urls): void
    {
        $urlModels = [];
        foreach ($urls as $index => $urlData) {
            // Validate required fields
            $requiredFields = ['url_type', 'url', 'name', 'slug'];
            foreach ($requiredFields as $field) {
                if (!isset($urlData[$field]) || empty($urlData[$field])) {
                    throw new \InvalidArgumentException("Missing required field '{$field}' for URL at index {$index}");
                }
            }

            $urlModels[] = [
                'urlable_type' => AccountType::class,
                'urlable_id' => $accountType->id,
                'url_type' => $urlData['url_type'],
                'url' => $urlData['url'],
                'url_p' => $urlData['url_p'] ?? null,
                'name' => $urlData['name'],
                'name_p' => $urlData['name_p'] ?? null,
                'slug' => $urlData['slug'],
                'description' => $urlData['description'] ?? null,
                'category_position' => $urlData['category_position'] ?? null,
                'option_category_id' => $urlData['option_category_id'],
                'broker_id' => $accountType->broker_id,
                'zone_id' => $urlData['zone_id'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (!empty($urlModels)) {
            DB::table('urls')->insert($urlModels);
        }
    }

    /**
     * Handle URL updates and deletions
     */
    public function handleUrlUpdates(AccountType $accountType, array $urls, array $urlsToDelete): void
    {
        // Delete URLs if specified
        if (!empty($urlsToDelete)) {
            DB::table('urls')->whereIn('id', $urlsToDelete)->delete();
        }

        // Update/Create URLs if provided
        if (!empty($urls)) {
            foreach ($urls as $urlData) {
                $urlModelData = [
                    'url' => $urlData['url'],
                    'url_p' => $urlData['url_p'] ?? null,
                    'url_type' => $urlData['url_type'],
                    'name' => $urlData['name'],
                    'name_p' => $urlData['name_p'] ?? null,
                    'slug' => $urlData['slug'],
                    'description' => $urlData['description'] ?? null,
                    'category_position' => $urlData['category_position'] ?? null,
                    'option_category_id' => $urlData['option_category_id'],
                    'broker_id' => $accountType->broker_id,
                    'zone_id' => $urlData['zone_id'] ?? null,
                    'updated_at' => now(),
                ];

                if (isset($urlData['id'])) {
                    // Update existing URL
                    DB::table('urls')->where('id', $urlData['id'])->update($urlModelData);
                } else {
                    // Create new URL
                    $urlModelData['urlable_type'] = AccountType::class;
                    $urlModelData['urlable_id'] = $accountType->id;
                    $urlModelData['created_at'] = now();
                    DB::table('urls')->insert($urlModelData);
                }
            }
        }
    }

    /**
     * Get account types by broker ID
     */
    public function getByBrokerId(int $brokerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('broker_id', $brokerId)->get();
    }

    /**
     * Get account types by broker type
     */
    public function getByBrokerType(string $brokerType): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('broker_type', $brokerType)->get();
    }

    /**
     * Get active account types
     */
    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    /**
     * Search account types by name
     */
    public function searchByName(string $search): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('name', 'like', '%' . $search . '%')->get();
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
       
        
        if ($request->has('broker_id')) {
            $query->where('broker_id', $request->broker_id);
        }

        if ($request->has('zone_code')) {
            $query->where('zone_code', function($q) use ($request){
                $q->where('zone_code', $request->zone_code);
            });
        }

        if ($request->has('broker_type')) {
            $query->where('broker_type', $request->broker_type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, Request $request): void
    {
        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by', 'order');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);
        }
    }
} 