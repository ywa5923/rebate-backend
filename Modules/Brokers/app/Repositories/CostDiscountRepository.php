<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\CostDiscount;

class CostDiscountRepository
{
    /**
     * @var CostDiscount
     */
    protected CostDiscount $model;

    /**
     * Inject model instance.
     * @param CostDiscount $model
     */
    public function __construct(CostDiscount $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new CostDiscount record.
     * @param array $data
     * @return CostDiscount
     */
    public function create(array $data): CostDiscount
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * Update an existing CostDiscount record using provided data.
     * Requires 'id' key in $data.
     * @param array $data
     * @return CostDiscount
     */
    public function update(array $data): CostDiscount
    {
        if (!array_key_exists('id', $data)) {
            throw new \InvalidArgumentException("'id' is required to update CostDiscount");
        }

        /** @var CostDiscount $costDiscount */
        $costDiscount = $this->model->newQuery()->findOrFail($data['id']);
        // Do not allow changing primary key inadvertently
        unset($data['id']);

        $costDiscount->update($data);
        return $costDiscount;
    }

    /**
     * Find a CostDiscount by id.
     * @param int $id
     * @return CostDiscount|null
     */
    public function find(int $id): ?CostDiscount
    {
        return $this->model->find($id);
    }

    /**
     * Get CostDiscounts filtered by broker, challenge and optional zone.
     * @param int $brokerId
     * @param int $challengeId
     * @param int|null $zoneId
     * @return Collection
     */
    public function findByBrokerAndChallenge(int $brokerId, int $challengeId, ?int $zoneId = null): Collection
    {
        $query = $this->model->newQuery()
            ->where('broker_id', $brokerId)
            ->where('challenge_id', $challengeId);

        if ($zoneId !== null) {
            $query->where('zone_id', $zoneId);
        } else {
            $query->whereNull('zone_id');
        }

        return $query->orderBy('id', 'desc')->get();
    }

    /**
     * Bulk upsert CostDiscount rows.
     * @param array $rows
     * @param array $uniqueBy
     * @return int Number of affected rows
     */
    public function upsertMany(array $rows, array $uniqueBy = ['broker_id', 'challenge_id', 'zone_id']): int
    {
        if (empty($rows)) {
            return 0;
        }

        return $this->model->newQuery()->upsert(
            $rows,
            $uniqueBy,
            ['broker_value', 'public_value', 'old_value', 'is_updated_entry', 'updated_at']
        );
    }

    /**
     * Delete CostDiscounts by broker.
     * @param int $brokerId
     * @return int Number of deleted rows
     */
    public function deleteByBroker(int $brokerId): int
    {
        return $this->model->newQuery()->where('broker_id', $brokerId)->delete();
    }

    /**
     * Delete CostDiscounts by challenge (optionally scoped to a broker and/or zone).
     * @param int $challengeId
     * @param int|null $brokerId
     * @param int|null $zoneId
     * @return int Number of deleted rows
     */
    public function deleteByChallenge(int $challengeId, ?int $brokerId = null, ?int $zoneId = null): int
    {
        $query = $this->model->newQuery()->where('challenge_id', $challengeId);

        if ($brokerId !== null) {
            $query->where('broker_id', $brokerId);
        }

        if ($zoneId !== null) {
            $query->where('zone_id', $zoneId);
        }

        return $query->delete();
    }

    /**
     * Update CostDiscounts by challenge (optionally scoped to a broker and/or zone).
     * @param int $challengeId
     * @param array $attributes
     * @param int|null $brokerId
     * @param int|null $zoneId
     * @return int Number of affected rows
     */
    public function updateByChallengeId(int $challengeId, array $attributes, ?int $brokerId = null, ?int $zoneId = null): int
    {
        $query = $this->model->newQuery()->where('challenge_id', $challengeId);

        if ($brokerId !== null) {
            $query->where('broker_id', $brokerId);
        }

        if ($zoneId !== null) {
            $query->where('zone_id', $zoneId);
        } else {
            // If zone not provided, update only invariant (null) to mimic findByBrokerAndChallenge
            $query->whereNull('zone_id');
        }

        return $query->update($attributes);
    }

    /**
     * Find CostDiscounts by challenge (optionally scoped to broker and zone).
     * @param int $challengeId
     * @param int|null $brokerId
     * @param int|null $zoneId
     * @return CostDiscount|null
     */
    public function findByChallengeId(int $challengeId, ?int $brokerId = null, ?int $zoneId = null): ?CostDiscount
    {
        $query = $this->model->newQuery()->where('challenge_id', $challengeId);

        if ($brokerId !== null) {
            $query->where('broker_id', $brokerId);
        }

        if ($zoneId !== null) {
            $query->where('zone_id', $zoneId);
        } else {
            $query->whereNull('zone_id');
        }

        return $query->orderBy('id', 'desc')->first();
    }

    public function createCostDiscount(int $challengeId,string $costDiscount, int $brokerId, bool $isAdmin,bool $isPlaceholder,?int $zoneId = null): CostDiscount
    {
        $field = ($isAdmin && !$isPlaceholder) ? 'public_value' : 'value';   
       return $this->create([
            'challenge_id' => $challengeId,
            'broker_id' => $brokerId,
            'zone_id' => $zoneId,
            $field => $costDiscount,
            'is_placeholder' => $isPlaceholder,
        ]);
    }
}


