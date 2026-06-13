<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\Challenge;
use Modules\Brokers\Models\ChallengeCategory;
use Modules\Brokers\Models\ChallengeMatrixValue;
use Modules\Brokers\Models\ChallengeStep;
use Modules\Brokers\Models\MatrixHeader;

class ChallengeRepository
{
    protected Challenge $model;

    const ZONE_ID_NULL = null;

    const BROKER_ID_NULL = null;

    const AMOUNT_ID_NULL = null;

    public function __construct(Challenge $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new challenge
     */
    public function create(array $data): Challenge
    {
        return $this->model->create($data);
    }

    /**
     * Find challenge by ID
     */
    public function findById(int $id): ?Challenge
    {
        return $this->model->find($id);
    }

    /**
     * Get matrix headers by group names
     */
    public function getMatrixHeadersByGroups(array $groupNames): Collection
    {
        return MatrixHeader::whereIn('group_name', $groupNames)->get();
    }

    /**
     * Get matrix header by slug and groups
     */
    public function getMatrixHeaderBySlugAndGroups(string $slug, array $groupNames): ?MatrixHeader
    {
        return MatrixHeader::where('slug', $slug)
            ->whereIn('group_name', $groupNames)
            ->first();
    }

    /**
     * Bulk insert challenge matrix values
     */
    public function insertChallengeMatrixValues(array $values): void
    {
        ChallengeMatrixValue::insert($values);
    }

    /**
     * Delete challenge matrix values by challenge ID
     */
    public function deleteChallengeMatrixValues(int $challengeId): void
    {
        ChallengeMatrixValue::where('challenge_id', $challengeId)->delete();
    }

    /**
     * Get challenge matrix values by challenge ID
     */
    public function getChallengeMatrixValues(int $challengeId, ?int $zoneId = null): Collection
    {
        return ChallengeMatrixValue::where('challenge_id', $challengeId)->where('zone_id', $zoneId)
            ->with(['row', 'column'])
            ->get();
    }

    /**
     * Check if challenge exists and return it
     */
    public function exists(bool $isPlaceholder, int $categoryId, int $stepId, ?int $amountId, ?int $brokerId = null, ?int $zoneId = null): ?Challenge
    {

        $qb = $this->model->newQuery()->where('is_placeholder', $isPlaceholder)
            ->where('challenge_category_id', $categoryId)
            ->where('challenge_step_id', $stepId);

        if (isset($zoneId)) {
            $qb->where('zone_id', $zoneId);
        } else {
            $qb->whereNull('zone_id');
        }

        if (isset($brokerId)) {
            $qb->where('broker_id', $brokerId);
        }

        if (isset($amountId)) {
            $qb->where('challenge_amount_id', $amountId);
        }

        return $qb->first();
    }

    /**
     * Get default category  id by broker category id and broker id
     * Default category and step are the ones defined by admin with broker_id=null that have same slugs with the ones
     *  cloned for the broker at registration time.

     */
    public function getPlaceholderCategoryId(int $brokerCategoryId, int $brokerId): ?int
    {
        return ChallengeCategory::query()
            ->whereNull('broker_id')
            ->where('slug', function ($q) use ($brokerCategoryId, $brokerId) {
                $q->select('slug')
                    ->from('challenge_categories')
                    ->where('id', $brokerCategoryId)
                    ->where('broker_id', $brokerId)
                    ->limit(1);
            })->value('id');
    }

    /**
     * Get default step id by broker step id and default category id received from getPlaceholderCategoryId method
     * Default category and step are the ones defined by admin with broker_id=null that have same slugs 
     * with the ones cloned for the broker at registration time.
     */
    public function getPlaceholderStepId(int $brokerStepId, int $defaultCategoryId): ?int
    {
        
        return ChallengeStep::query()
            ->whereHas('challengeCategory', fn ($q) => $q->whereNull('broker_id'))
            ->where('challenge_category_id', $defaultCategoryId)
            ->where('slug', function ($q) use ($brokerStepId) {
                $q->select('cs.slug')
                    ->from('challenge_steps as cs')
                    //->join('challenge_categories as cc', 'cc.id', '=', 'cs.challenge_category_id')
                    ->where('cs.id', $brokerStepId)
                    //->where('cc.broker_id', $brokerId)

                    ->limit(1);
            })
            ->value('id');
    }

    /**
     * Add challenges for user
     */
    public function syncChallengesForAmounts(array $amountIds,bool $isPublished, int $categoryId, int $stepId, int $brokerId, ?int $zoneId = null): array
    {
        $insertData = [];

        $existingChallenges = $this->model->newQuery()
            ->where('is_placeholder', false)
            ->where('challenge_category_id', $categoryId)
            ->where('challenge_step_id', $stepId)
            ->where('broker_id', $brokerId)
            ->where('zone_id', $zoneId)
            ->whereIn('challenge_amount_id', $amountIds)
            ->get();
        //update is_published for the existing challenges
        $existingChallenges->each(function ($challenge) use ($isPublished) {
            $challenge->is_published = $isPublished;
            $challenge->save();
        });

        foreach ($amountIds as $amountId) {

            if ($existingChallenges->contains('challenge_amount_id', $amountId)) {
                continue;
            }

            $insertData[] = [
                'is_placeholder' => false,
                'is_published' => $isPublished,
                'challenge_category_id' => $categoryId,
                'challenge_step_id' => $stepId,
                'challenge_amount_id' => $amountId,
                'broker_id' => $brokerId,
                'zone_id' => $zoneId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($this->model->newQuery()->insert($insertData)) {
            return $this->model->newQuery()
                ->where([
                    'is_placeholder' => false,
                    'challenge_category_id' => $categoryId,
                    'challenge_step_id' => $stepId,
                    'broker_id' => $brokerId,
                    'zone_id' => $zoneId,
                ])
                ->whereIn('challenge_amount_id', $amountIds)
                ->pluck('id')
                ->toArray();
        } else {
            return [];
        }
    }
}
