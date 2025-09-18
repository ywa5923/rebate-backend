<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Challenge;
use Modules\Brokers\Models\ChallengeMatrixValue;
use Modules\Brokers\Models\MatrixHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ChallengeRepository
{
    protected Challenge $model;

    public function __construct(Challenge $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new challenge
     * @param array $data
     * @return Challenge
     */
    public function create(array $data): Challenge
    {
        return $this->model->create($data);
    }

    /**
     * Find challenge by ID
     * @param int $id
     * @return Challenge|null
     */
    public function findById(int $id): ?Challenge
    {
        return $this->model->find($id);
    }

    /**
     * Get matrix headers by group names
     * @param array $groupNames
     * @return Collection
     */
    public function getMatrixHeadersByGroups(array $groupNames): Collection
    {
        return MatrixHeader::whereIn('group_name', $groupNames)->get();
    }

    /**
     * Get matrix header by slug and groups
     * @param string $slug
     * @param array $groupNames
     * @return MatrixHeader|null
     */
    public function getMatrixHeaderBySlugAndGroups(string $slug, array $groupNames): ?MatrixHeader
    {
        return MatrixHeader::where('slug', $slug)
            ->whereIn('group_name', $groupNames)
            ->first();
    }

    /**
     * Bulk insert challenge matrix values
     * @param array $values
     * @return void
     */
    public function insertChallengeMatrixValues(array $values): void
    {
        ChallengeMatrixValue::insert($values);
    }

    /**
     * Delete challenge matrix values by challenge ID
     * @param int $challengeId
     * @return void
     */
    public function deleteChallengeMatrixValues(int $challengeId,?int $zoneId=null): void
    {
        ChallengeMatrixValue::where('challenge_id', $challengeId)->where('zone_id', $zoneId)->delete();
    }

    /**
     * Get challenge matrix values by challenge ID
     * @param int $challengeId
     * @return Collection
     */
    public function getChallengeMatrixValues(int $challengeId,?int $zoneId=null): Collection
    {
        return ChallengeMatrixValue::where('challenge_id', $challengeId)->where('zone_id', $zoneId)
            ->with(['row', 'column'])
            ->get();
    }


    /**
     * Check if challenge exists and return it
     * @param bool $isPlaceholder
     * @param int $categoryId
     * @param int $stepId
     * @param int|null $amountId
     * @param int $brokerId
     * @return Challenge|null
     */
    public function exists(bool $isPlaceholder, int $categoryId, int $stepId, ?int $amountId, int $brokerId): ?Challenge
    {
        return $this->model->where('is_placeholder', $isPlaceholder)
            ->where('challenge_category_id', $categoryId)
            ->where('challenge_step_id', $stepId)
            ->where('challenge_amount_id', $amountId)
            ->where('broker_id', $brokerId)
            ->first();
    }
}