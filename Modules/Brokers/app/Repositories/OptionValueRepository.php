<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\OptionValue;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Transformers\DynamicOptionValueCollection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OptionValueRepository 
{
    protected OptionValue $model;

    public function __construct(OptionValue $model)
    {
        $this->model = $model;
    }

    public function getUniqueList($language, $slug, $zoneCondition)
    {
        $results = [];
        //dd($language,$slug,$zoneCondition);
        OptionValue::with(["translations" => function (Builder $query) use ($language) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
             $query->where(...$language);
        }])->where("option_slug", "=", $slug)->where(function (Builder $query) use ($zoneCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
           
            $query->where(...$zoneCondition)->orWhere('is_invariant', true);
        })
         
            ->chunk(100, function ($options) use (&$results) {
                $collection = new DynamicOptionValueCollection($options);
                $list = [];
                foreach ($collection->resolve() as $option) {
                
                //if the optionValue contain a link we will keep only the text
                preg_match('/<a[^>]*>(.*?)<\/a>/', $option["value"], $match);
                    $optionValue = ($match) ? $match[1] : $option["value"];

                //$items=explode(",",$option["value"]);
                    $items = explode(",", $optionValue);
                    foreach ($items as $item) {
                        if (!array_key_exists(trim($item), $list) && trim($item) !== "")
                            $list[trim($item)] = trim($item);
                    }
                }
                $results = array_merge($results, $list);
         });

         return array_unique($results);
    }

    // ===== NEW ADDED METHODS FOR CRUD OPERATIONS =====

    /**
     * Get paginated option values with filters
     */
    public function getOptionValues(Request $request): LengthAwarePaginator|Collection
    {
        if ($request->has('language_code')) {
            $locale = $request->language_code;
        } else {
            $locale = 'en';
        }
        $query = $this->model->with(['broker', 'option', 'zone', 'translations' => function ($query) use ($locale) {
            $query->where('language_code', $locale);
        }]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

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
     * Get option value by ID with relations
     */
    public function findById(int $id): ?OptionValue
    {
        return $this->model->with(['broker', 'option', 'zone', 'translations'])->find($id);
    }

    /**
     * Get option value by ID without relations
     */
    public function findByIdWithoutRelations(int $id): ?OptionValue
    {
        return $this->model->find($id);
    }

    /**
     * Create new option value
     */
    public function create(array $data): OptionValue
    {
        return $this->model->create($data);
    }

    /**
     * Bulk create multiple option values
     */
    public function bulkCreate(array $data): bool
    {
       
        return $this->model->insert($data);
    }

    /**
     * Update option value
     */
    public function update(OptionValue $optionValue, array $data): bool
    {
        return $optionValue->update($data);
    }

    /**
     * Bulk update multiple option values
     */
    public function bulkUpdate(array $updatesByCondition, int $brokerId): bool
    {
        //         -- Query 1: Single UPDATE with CASE statements
        // UPDATE option_values SET 
        //     value = CASE id 
        //         WHEN 1 THEN '200' 
        //         WHEN 2 THEN '1000' 
        //         END,
        //     public_value = CASE id 
        //         WHEN 1 THEN '$200' 
        //         WHEN 2 THEN '1:1000' 
        //         END,
        //     updated_at = CASE id 
        //         WHEN 1 THEN '2024-01-01 00:00:00' 
        //         WHEN 2 THEN '2024-01-01 00:00:00' 
        //         END
        // WHERE id IN (1, 2);
        // Validate that all option values belong to the broker
        $optionValueIds = array_keys($updatesByCondition);
        $existingOptionValues = $this->model->whereIn('id', $optionValueIds)
            ->where('broker_id', $brokerId)
            ->pluck('id')
            ->toArray();

        if (count($existingOptionValues) !== count($optionValueIds)) {
            throw new \Exception('Some option values not found or do not belong to the broker');
        }

        // Get all unique columns that need to be updated
        $allColumns = [];
        foreach ($updatesByCondition as $data) {
            $allColumns = array_merge($allColumns, array_keys($data));
        }
        $allColumns = array_unique($allColumns);

        // Build CASE statements for each column
        $caseStatements = [];
        $bindings = [];

        foreach ($allColumns as $column) {
            $caseStatements[$column] = "CASE id ";
            
            foreach ($updatesByCondition as $id => $data) {
                if (isset($data[$column])) {
                    $caseStatements[$column] .= "WHEN ? THEN ? ";
                    $bindings[] = $id;
                    $bindings[] = $data[$column];
                }
            }
            
            $caseStatements[$column] .= "END";
        }

        // Build the SQL query
        $sql = "UPDATE option_values SET ";
        $updateParts = [];

        foreach ($caseStatements as $column => $caseStatement) {
            $updateParts[] = "{$column} = {$caseStatement}";
        }

        $sql .= implode(', ', $updateParts);
        $sql .= " WHERE id IN (" . implode(',', array_fill(0, count($optionValueIds), '?')) . ")";
        $bindings = array_merge($bindings, $optionValueIds);

        try {
            $result = DB::update($sql, $bindings);
            
            // Only log in development or when explicitly enabled
            if (config('app.debug') || config('app.env') === 'local') {
                Log::info('Bulk update completed', [
                    'broker_id' => $brokerId,
                    'records_updated' => $result,
                    'total_records' => count($optionValueIds)
                ]);
            }
            
            return $result > 0;
        } catch (\Exception $e) {
            // Always log errors
            Log::error('Bulk update failed', [
                'broker_id' => $brokerId,
                'error' => $e->getMessage(),
                'records_count' => count($optionValueIds)
            ]);
            throw $e;
        }
    }

    /**
     * Alternative bulk update using upsert (more efficient for large datasets)
     */
    public function bulkUpdateUpsert(array $updatesByCondition, int $brokerId): bool
    {
        // Validate that all option values belong to the broker
        $optionValueIds = array_keys($updatesByCondition);
        $existingOptionValues = $this->model->whereIn('id', $optionValueIds)
            ->where('broker_id', $brokerId)
            ->pluck('id')
            ->toArray();

        if (count($existingOptionValues) !== count($optionValueIds)) {
            throw new \Exception('Some option values not found or do not belong to the broker');
        }

        // Prepare data for upsert
        $upsertData = [];
        foreach ($updatesByCondition as $id => $data) {
            $data['id'] = $id; // Include ID for upsert
            $upsertData[] = $data;
        }

        // Use upsert to update existing records
        return $this->model->upsert(
            $upsertData,
            ['id'], // Unique columns
            array_keys($updatesByCondition[array_key_first($updatesByCondition)]) // Update columns
        ) > 0;
    }

    /**
     * Delete option value
     */
    public function delete(OptionValue $optionValue): bool
    {
        return $optionValue->delete();
    }

    /**
     * Get brokers for form dropdown
     */
    public function getBrokersForForm(): Collection
    {
        return DB::table('brokers')->select('id', 'registration_language as name')->get();
    }

    /**
     * Get broker options for form dropdown
     */
    public function getBrokerOptionsForForm(): Collection
    {
        return DB::table('broker_options')->select('id', 'name', 'slug')->get();
    }

    /**
     * Get zones for form dropdown
     */
    public function getZonesForForm(): Collection
    {
        return DB::table('zones')->select('id', 'name')->get();
    }

    /**
     * Get option values by broker ID
     */
    public function getByBrokerId(int $brokerId): Collection
    {
        return $this->model->where('broker_id', $brokerId)->get();
    }

    /**
     * Get option values by broker ID and option slugs
     */
    public function getByBrokerIdAndOptionSlugs(int $brokerId, array $optionSlugs): Collection
    {
        return $this->model->where('broker_id', $brokerId)
            ->whereIn('option_slug', $optionSlugs)
            ->get();
    }

    /**
     * Get option values by broker ID and specific IDs
     */
    public function getByBrokerIdAndIds(int $brokerId, array $ids): Collection
    {
        return $this->model->where('broker_id', $brokerId)
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Get option values by broker option ID
     */
    public function getByBrokerOptionId(int $brokerOptionId): Collection
    {
        return $this->model->where('broker_option_id', $brokerOptionId)->get();
    }

    /**
     * Get option values by status
     */
    public function getByStatus(bool $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Search option values by value
     */
    public function searchByValue(string $search): Collection
    {
        return $this->model->where('value', 'like', "%{$search}%")->get();
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('broker_id')) {
            $query->where('broker_id', $request->broker_id);
        }

        if ($request->has('broker_option_id')) {
            $query->where('broker_option_id', $request->broker_option_id);
        }

        if ($request->has('option_slug')) {
            $query->where('option_slug', $request->option_slug);
        }

        if ($request->has('zone_code')) {
            $query->where(function ($q) use ($request) {
                $q->where('is_invariant', true)
                    ->orWhereHas('zone', function ($subQ) use ($request) {
                        $subQ->where('zone_code', $request->zone_code);
                    });
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('value', 'like', "%{$search}%")
                    ->orWhere('public_value', 'like', "%{$search}%")
                    ->orWhere('option_slug', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortFields = ['option_slug', 'value', 'status', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }
}
