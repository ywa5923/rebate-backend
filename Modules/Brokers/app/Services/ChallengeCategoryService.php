<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\ChallengeCategoryRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\ChallengeCategory;
use Modules\Brokers\Repositories\ChallengeStepRepository;
use Modules\Brokers\Repositories\ChallengeAmountRepository;
use Modules\Brokers\Enums\ChallengeTabEnum;
use Modules\Brokers\Models\ChallengeStep;
use Modules\Brokers\Models\ChallengeAmount;
use Modules\Brokers\Models\ChallengeMatrixValue;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Models\Challenge;

class ChallengeCategoryService
{


    public function __construct(
        protected ChallengeCategoryRepository $repository,
        protected ChallengeStepRepository $challengeStepRepository,
        protected ChallengeAmountRepository $challengeAmountRepository
    ) {}




    /**
     * Get paginated challenge categories with filters
     */
    public function getChallengeCategories(?int $broker_id = null): LengthAwarePaginator|Collection
    {

        return $this->repository->getChallengeCategories($broker_id);
    }

    /**
     * Get challenge category by ID
     */
    public function getChallengeCategoryById(int $id): ?ChallengeCategory
    {
        return $this->repository->findById($id);
    }
    /**
     * Get challenge category by ID and broker ID
     * @param int $id
     * @param int $broker_id
     * @return ?ChallengeCategory
     */
    public function getChallengeCategoryByIdAndBroker(int $id, int $broker_id): ?ChallengeCategory
    {
        return $this->repository->findByIdWithoutRelations($id, $broker_id);
    }
    /**
     * Create new challenge category
     */
    public function createChallengeCategory(array $data): ChallengeCategory
    {
        return DB::transaction(function () use ($data) {
            try {
                // Validate data
                $validatedData = $this->validateChallengeCategoryData($data);

                // Create challenge category
                $challengeCategory = $this->repository->create($validatedData);

                return $challengeCategory->load(['steps', 'amounts']);
            } catch (\Exception $e) {
                Log::error('ChallengeCategoryService createChallengeCategory error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update challenge category
     */
    public function updateChallengeCategory(int $id, array $data): ChallengeCategory
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $challengeCategory = $this->repository->findByIdWithoutRelations($id);

                if (!$challengeCategory) {
                    throw new \Exception('Challenge category not found');
                }

                // Validate data
                $validatedData = $this->validateChallengeCategoryData($data, true);

                // Update challenge category
                $this->repository->update($challengeCategory, $validatedData);

                return $challengeCategory->load(['steps', 'amounts']);
            } catch (\Exception $e) {
                Log::error('ChallengeCategoryService updateChallengeCategory error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete challenge category
     */
    public function deleteChallengeCategory(int $id, ?int $broker_id = null): bool
    {

        $challengeCategory = $this->repository->findByIdWithoutRelations($id, $broker_id);

        if (!$challengeCategory) {
            throw new \Exception('Challenge category not found');
        }
        return DB::transaction(function () use ($challengeCategory) {

            //delete challenges and matrix related data
            $challenges = $challengeCategory->challenges()->get();
            // foreach($challenges as $challenge){
            //     $challenge->delete();
            //     $challenge->challengeMatrixValues()->delete();
            //     $challenge->urls()->delete();
            // }

            // Delete challenges and their related data
            // $challengeCategory->challenges()
            //     ->select('id') // lighter
            //     ->chunkById(500, function (Collection $chunk) {
            //         $ids = $chunk->pluck('id');

            //         // child tables first
            //         ChallengeMatrixValue::whereIn('challenge_id', $ids)->delete();
            //         Url::whereIn('urlable_id', $ids)
            //             ->where('urlable_type', Challenge::class)
            //             ->delete();

            //         // then challenges
            //         Challenge::whereIn('id', $ids)->delete();
            //     });

            ChallengeMatrixValue::whereIn('challenge_id', $challenges->pluck('id'))->delete();
            Url::whereIn('urlable_id', $challenges->pluck('id'))
                ->where('urlable_type', Challenge::class)
                ->delete();
            Challenge::whereIn('id', $challenges->pluck('id'))->delete();
            // Delete related steps and amounts first
            $challengeCategory->steps()->delete();
            $challengeCategory->amounts()->delete();

            // Delete challenge category
            $this->repository->delete($challengeCategory);


            return true;
        });
    }

    public function deleteChallengeCategoryStep(int $id, ?int $broker_id = null): bool
    {
        $challengeStep = $this->challengeStepRepository->findById($id, $broker_id);
        if (!$challengeStep) {
            throw new \Exception('Challenge step not found');
        }
        return DB::transaction(function () use ($challengeStep) {

            //delete challenges and matrix related data
            $challenges = $challengeStep->challenges()->get();
            // 
            ChallengeMatrixValue::whereIn('challenge_id', $challenges->pluck('id'))->delete();
            Url::whereIn('urlable_id', $challenges->pluck('id'))
                ->where('urlable_type', Challenge::class)
                ->delete();
            Challenge::whereIn('id', $challenges->pluck('id'))->delete();


            $challengeStep->delete();
            return true;
        });
    }

    public function deleteChallengeCategoryAmount(int $id, ?int $broker_id = null): bool
    {
        
            $challengeAmount = $this->challengeAmountRepository->findById($id, $broker_id);
            if (!$challengeAmount) {
                throw new \Exception('Challenge amount not found');
            }
            return DB::transaction(function () use ($challengeAmount) {
            //delete challenges and matrix related data
            $challenges = $challengeAmount->challenges()->get();
            ChallengeMatrixValue::whereIn('challenge_id', $challenges->pluck('id'))->delete();
            Url::whereIn('urlable_id', $challenges->pluck('id'))
                ->where('urlable_type', Challenge::class)
                ->delete();
            Challenge::whereIn('id', $challenges->pluck('id'))->delete();
           
            $challengeAmount->delete();
            return true;
        });
    }

    /**
     * Save the full ordering of tabs (categories, steps or amounts) for a broker.
     * $tabIds is the array of entity IDs in the desired final order.
     *
     * For 'category', updates challenge_categories.order (scoped by broker_id).
     * For 'step', updates challenge_steps.order (scoped by broker via category).
     * For 'amount', updates challenge_amounts.order (scoped by broker via category).
     *
     * Ensures items belong to the given broker via their category.
     *
     * @param array<int,int> $tabIds
     * @param int $brokerId
     * @param ChallengeTabEnum|string $tabType
     * @return bool
     */
    public function saveChallengeTabOrder(array $tabIds, int $brokerId, ChallengeTabEnum|string $tabType): bool
    {
        if (empty($tabIds)) {
            return true;
        }
        $tabTypeEnum = $tabType instanceof ChallengeTabEnum ? $tabType : ChallengeTabEnum::from($tabType);
        // Normalize and deduplicate while preserving order
        $orderedIds = array_values(array_unique(array_map('intval', $tabIds)));

        return DB::transaction(function () use ($orderedIds, $brokerId, $tabTypeEnum) {
           
            if ($tabTypeEnum === ChallengeTabEnum::CATEGORY) {
                $existingRows = ChallengeCategory::query()
                    ->whereIn('id', $orderedIds)
                    ->where('broker_id', $brokerId)
                    ->get(['id', 'name', 'slug'])
                    ->keyBy('id');
                $rows = [];
                foreach ($orderedIds as $position => $id) {
                    $row = $existingRows->get($id);
                    if (!$row) {
                        throw new \InvalidArgumentException("Category $id not found for broker $brokerId");
                    }
                    $rows[] = [
                        'id' => $id,
                        'name' => $row->name,
                        'slug' => $row->slug,
                        'order' => $position + 1,
                        'broker_id' => $brokerId,
                        'updated_at' => now(),
                    ];
                }
                ChallengeCategory::upsert($rows, ['id'], ['order', 'updated_at']);
            } elseif ($tabTypeEnum === ChallengeTabEnum::STEP) {
                $existingRows = ChallengeStep::query()
                    ->whereIn('id', $orderedIds)
                    ->whereHas('challengeCategory', fn($q) => $q->where('broker_id', $brokerId))
                    ->get(['id', 'name', 'slug', 'challenge_category_id'])
                    ->keyBy('id');
                $rows = [];
                foreach ($orderedIds as $position => $id) {
                    $row = $existingRows->get($id);
                    if (!$row) {
                        throw new \InvalidArgumentException("Step $id not found for broker $brokerId");
                    }
                    $rows[] = [
                        'id' => $id,
                        'name' => $row->name,
                        'slug' => $row->slug,
                        'challenge_category_id' => $row->challenge_category_id,
                        'order' => $position + 1,
                        'updated_at' => now(),
                    ];
                }
                ChallengeStep::upsert($rows, ['id'], ['order', 'updated_at']);
            } else {
                $existingRows = ChallengeAmount::query()
                    ->whereIn('id', $orderedIds)
                    ->whereHas('challengeCategory', fn($q) => $q->where('broker_id', $brokerId))
                    ->get(['id', 'amount', 'currency', 'challenge_category_id'])
                    ->keyBy('id');
                $rows = [];
                foreach ($orderedIds as $position => $id) {
                    $row = $existingRows->get($id);
                    if (!$row) {
                        throw new \InvalidArgumentException("Amount $id not found for broker $brokerId");
                    }
                    $rows[] = [
                        'id' => $id,
                        'amount' => $row->amount,
                        'currency' => $row->currency,
                        'challenge_category_id' => $row->challenge_category_id,
                        'order' => $position + 1,
                        'updated_at' => now(),
                    ];
                }
                ChallengeAmount::upsert($rows, ['id'], ['order', 'updated_at']);
            }
            return true;
        });
    }

    /**
     * Validate challenge category data
     */
    public function validateChallengeCategoryData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * Add a challenge tab,i.e category, step, amount
     * clone a default tab by id to a broker challenge tab 
     * @param string $tab_type
     * @param int $default_tab_id_to_clone
     * @param int $tab_order
     * @param int $broker_id
     * @param int $broker_challenge_category_id
     * @return bool
     */
    public function addChallengeTabToBroker(
        ChallengeTabEnum $tab_type,
        int $default_tab_id_to_clone,
        int $tab_order,
        int $broker_id,
        ?int $broker_challenge_category_id = null
    ): ChallengeCategory|ChallengeStep|ChallengeAmount {

        return match ($tab_type) {
            ChallengeTabEnum::CATEGORY => $this->repository->cloneCategory($default_tab_id_to_clone, $tab_order, $broker_id),
            ChallengeTabEnum::STEP => $this->challengeStepRepository->cloneStep($default_tab_id_to_clone,  $tab_order, $broker_challenge_category_id),
            ChallengeTabEnum::AMOUNT => $this->challengeAmountRepository->cloneAmount($default_tab_id_to_clone,  $tab_order, $broker_challenge_category_id),
        };
    }
}
