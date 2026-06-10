<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\CostDiscount;
use Modules\Translations\Repositories\TranslationRepository;

class CostDiscountRepository
{
    public function __construct(
        protected TranslationRepository $translationRepository,
        protected CostDiscount $model)
    {
    }

    /**
     * Create a new CostDiscount record.
     */
    public function create(array $data): CostDiscount
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * Update an existing CostDiscount record using provided data.
     * Requires 'id' key in $data.
     */
    public function update(array $data): CostDiscount
    {
        if (! array_key_exists('id', $data)) {
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
     */
    public function find(int $id): ?CostDiscount
    {
        return $this->model->find($id);
    }

    /**
     * Get CostDiscounts filtered by broker, challenge and optional zone.
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
     *
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
     *
     * @return int Number of deleted rows
     */
    public function deleteByBroker(int $brokerId): int
    {
        return $this->model->newQuery()->where('broker_id', $brokerId)->delete();
    }

    /**
     * Delete CostDiscounts by challenge (optionally scoped to a broker and/or zone).
     *
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
     *
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
     */
    public function findByChallengeId(int $challengeId, ?int $brokerId = null, ?int $zoneId = null): ?CostDiscount
    {
        $query = $this->model->newQuery()->where('challenge_id', $challengeId);

        if ($brokerId !== null) {
            $query->where('broker_id', $brokerId);
        } else {
            $query->whereNull('broker_id');
        }

        //zone id is moved to the challenge tables
        // if ($zoneId !== null) {
        //     $query->where('zone_id', $zoneId);
        // } else {
        //     $query->whereNull('zone_id');
        // }

        return $query->orderBy('id', 'desc')->first();
    }

    public function createCostDiscount(int $challengeId, string $costDiscount, int $brokerId, bool $isAdmin, bool $isPlaceholder, ?int $zoneId = null): CostDiscount
    {
        $field = ($isAdmin && ! $isPlaceholder) ? 'public_value' : 'value';

        return $this->create([
            'challenge_id' => $challengeId,
            'broker_id' => $brokerId,
            'zone_id' => $zoneId,
            $field => $costDiscount,
            'is_placeholder' => $isPlaceholder,
        ]);
    }

    /**
     * Upsert cost discount (update or insert)
     */
    public function upsertCostDiscount(
        int $challengeId,
        ?string $costDiscount,
        ?int $brokerId,
        bool $isAdmin,
        bool $isPlaceholder,
        ?int $zoneId = null,
    ): void {
        // Check if record already exists
        $existingCostDiscount = $this->findByChallengeId($challengeId, $brokerId, $zoneId);

        if ($existingCostDiscount) {
            // Get the current value based on admin/placeholder status
            $oldDiscountValue = $isAdmin ? $existingCostDiscount->public_value : $existingCostDiscount->value;

            // Compare values and update if different
            if ($oldDiscountValue != $costDiscount && ! is_null($costDiscount)) {
                $updateData = [
                    ($isAdmin || $isPlaceholder) ? null : 'previous_value' => $oldDiscountValue,
                    $isAdmin && ! $isPlaceholder ? 'public_value' : 'value' => $costDiscount,
                    'is_updated_entry' => ($isAdmin || $isPlaceholder) ? false : true,
                ];

                // Remove null keys
                $updateData = array_filter($updateData, function ($key) {
                    return $key !== null;
                }, ARRAY_FILTER_USE_KEY);

                $existingCostDiscount->update($updateData);
            } elseif (is_null($costDiscount)) {
                $existingCostDiscount->delete();
            }
        } else {
            // Create new record if cost discount is not empty
            if (! empty($costDiscount)) {
                $field = ($isAdmin && ! $isPlaceholder) ? 'public_value' : 'value';

                $this->create([
                    'challenge_id' => $challengeId,
                    'broker_id' => $brokerId,
                    'zone_id' => $zoneId,
                    $field => $costDiscount,
                    'is_placeholder' => $isPlaceholder,
                    'is_updated_entry' => $isAdmin ? 0 : 1,
                ]);
            }
        }
    }

    public function cloneCostDiscounts(int $challenge_id, array $new_challenge_ids, int $broker_id, bool $isAdmin, ?int $zone_id = null): bool
    {
        $discountToClone = $this->model->where('challenge_id', $challenge_id)
            ->where('broker_id', $broker_id)->where('zone_id', $zone_id)->first();
        if (! $discountToClone) {
            return false;
        }

        $insertData = [];
        $now = now();
        $attributesToClone = $discountToClone->toArray();
        unset($attributesToClone['id']);

        foreach ($new_challenge_ids as $new_challenge_id) {

            //if a discount exists for this challenge, copy public value from existing discount and create a new batch of insert data
            $challengeDiscount = $this->model->where('challenge_id', $new_challenge_id)->where('broker_id', $broker_id)->where('zone_id', $zone_id)->first();
            if ($challengeDiscount) {
                $isUpdatedEntry = $challengeDiscount->value == $attributesToClone['value'] ? 0 : 1;

                if ($isAdmin) {
                    $challengeDiscount->update([
                        'public_value' => $attributesToClone['public_value'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    $challengeDiscount->update([
                        'value' => $attributesToClone['value'],
                        'is_updated_entry' => $isUpdatedEntry,
                        'previous_value' => $challengeDiscount->value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            } else {
                if ($isAdmin) {
                    //clone only public_value which exist in $attributesToClone, other keys are overwritten
                    $insertData[] = array_merge($attributesToClone, [
                        'challenge_id' => $new_challenge_id,
                        'is_updated_entry' => 0,
                        'previous_value' => null,
                        'value' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    //clone only value which exist in $attributesToClone, other keys are overwritten
                    $insertData[] = array_merge($attributesToClone, [
                        'challenge_id' => $new_challenge_id,
                        'is_updated_entry' => 0,
                        'previous_value' => null,
                        'public_value' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        //first delete translations
        // $this->translationRepository->deleteByTranslationableTypeAndIds(CostDiscount::class, $existingChallengeDiscountIds);
        //TO DO: add some observer to make again the translations for the new discounts

        return $this->model->newQuery()->insert($insertData);

    }
}
