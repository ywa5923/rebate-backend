<?php

namespace Modules\Brokers\Repositories;

use App\Utilities\ModelHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\DTOs\OptionValueFilters;
use Modules\Brokers\Models\OptionValue;
//use Illuminate\Database\Query\Builder;
use Modules\Brokers\Transformers\DynamicOptionValueCollection;

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
        OptionValue::with(['translations' => function (Builder $query) use ($language) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder $query */
            $query->where(...$language);
        }])->where('option_slug', '=', $slug)->where(function (Builder $query) use ($zoneCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder $query */
            $query->where(...$zoneCondition)->orWhere('is_invariant', true);
        })

            ->chunk(100, function ($options) use (&$results) {
                $collection = new DynamicOptionValueCollection($options);
                $list = [];
                foreach ($collection->resolve() as $option) {

                    //if the optionValue contain a link we will keep only the text
                    preg_match('/<a[^>]*>(.*?)<\/a>/', $option['value'], $match);
                    $optionValue = ($match) ? $match[1] : $option['value'];

                    //$items=explode(",",$option["value"]);
                    $items = explode(',', $optionValue);
                    foreach ($items as $item) {
                        if (! array_key_exists(trim($item), $list) && trim($item) !== '') {
                            $list[trim($item)] = trim($item);
                        }
                    }
                }
                $results = array_merge($results, $list);
            });

        return array_unique($results);
    }

    /**
     * Get paginated option values with filters
     */
    public function getOptionValues(OptionValueFilters $filters, int $broker_id): LengthAwarePaginator|Collection
    {
        if (isset($filters->base->languageCode)) {
            $locale = $filters->base->languageCode;
        } else {
            $locale = 'en';
        }
        $query = $this->model->with(['broker', 'option', 'zone', 'translations' => function ($query) use ($locale) {
            $query->where('language_code', $locale);
        }])->where('broker_id', $broker_id);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $this->applySorting($query, $filters);

        if ($filters->shouldPaginate) {
            return $query->paginate($filters->base->perPage, ['*'], 'page', $filters->base->page);
        }

        return $query->get();
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
            $caseStatements[$column] = 'CASE id ';
            $hasValues = false;

            foreach ($updatesByCondition as $id => $data) {
                if (array_key_exists($column, $data)) {
                    // For metadata column, use CAST to JSON for proper handling
                    if ($column === 'metadata') {
                        $caseStatements[$column] .= 'WHEN ? THEN CAST(? AS JSON) ';
                    } else {
                        $caseStatements[$column] .= 'WHEN ? THEN ? ';
                    }
                    $bindings[] = $id;

                    // JSON encode array values (like metadata) for proper MySQL JSON column handling
                    $value = $data[$column];
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    $bindings[] = $value;
                    $hasValues = true;
                }
            }

            $caseStatements[$column] .= 'END';

            // Skip columns with no values to avoid empty CASE statements
            if (! $hasValues) {
                unset($caseStatements[$column]);
            }
        }

        // Build the SQL query
        $sql = 'UPDATE option_values SET ';
        $updateParts = [];

        foreach ($caseStatements as $column => $caseStatement) {
            $updateParts[] = "{$column} = {$caseStatement}";
        }

        $sql .= implode(', ', $updateParts);
        $sql .= ' WHERE id IN ('.implode(',', array_fill(0, count($optionValueIds), '?')).')';
        $bindings = array_merge($bindings, $optionValueIds);

        try {
            $result = DB::update($sql, $bindings);

            return $result > 0;
        } catch (\Exception $e) {
            // Always log errors

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
     * Get option values by optionable ID
     */
    public function getByOptionableId(int $optionableId, string $optionableType): Collection
    {
        $optionableType = ModelHelper::getModelClassFromSlug($optionableType);

        return $this->model->where('optionable_id', $optionableId)->where('optionable_type', $optionableType)->get();
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
    private function applyFilters(Builder $query, OptionValueFilters $filters): void
    {
        if ($filters->visibileFor === 'broker') {
            $query->where('broker_can_see', true);
        }

        if (isset($filters->entityType)) {
            if ($filters->entityType == 'Broker') {
                //For backward compatibility with old data
                $query->where(function ($q) use ($filters) {
                    $q->where('optionable_type', ModelHelper::getModelClassFromSlug($filters->entityType))
                        ->orWhereNull('optionable_type');
                });
            } else {
                $query->where('optionable_type', ModelHelper::getModelClassFromSlug($filters->entityType));
            }
        }

        if (isset($filters->entityId)) {
            $query->where('optionable_id', $filters->entityId);
        }

        if (isset($filters->brokerOptionId)) {
            $query->where('broker_option_id', $filters->brokerOptionId);
        }

        if (isset($filters->optionSlug)) {
            $query->where('option_slug', $filters->optionSlug);
        }

        if (isset($filters->categoryId)) {

            $query->whereHas('option', function ($q) use ($filters) {
                $q->where('option_category_id', $filters->categoryId);
            });
        }

        if (isset($filters->base->zoneCode)) {
            $query->where(function ($q) use ($filters) {
                $q->where('is_invariant', true)->orWhere('zone_code', $filters->base->zoneCode);

                // ->orWhereHas('zone', function ($subQ) use ($filters) {
                //     $subQ->where('zone_code', $filters['zone_code']);
                // });
            });
        } else {
            $query->where('zone_id', null)->where('zone_code', null);
        }

        if (isset($filters->search)) {
            $search = $filters->search;
            $query->where(function ($q) use ($search) {
                $q->where('value', 'like', "%{$search}%")
                    ->orWhere('public_value', 'like', "%{$search}%");
                //->orWhere('option_slug', 'like', "%{$search}%");
            });
        }

        $query->with('translations', function ($q) use ($filters) {

            $languageCode = isset($filters->base->languageCode) ? $filters->base->languageCode : 'en';
            $q->where('language_code', $languageCode);
        });

    }

    /**
     * Apply sorting to query
     */
    private function applySorting(Builder $query, OptionValueFilters $filters): void
    {
        $sortBy = isset($filters->base->sortBy) ? $filters->base->sortBy : 'created_at';
        $sortDirection = isset($filters->base->sortDirection) ? $filters->base->sortDirection : 'desc';

        $allowedSortFields = ['option_slug', 'value', 'public_value', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }
}
