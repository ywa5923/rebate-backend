<?php

namespace Modules\Brokers\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Brokers\Enums\ChallengeTabEnum;
use Modules\Brokers\Models\Challenge;
use Modules\Brokers\Models\ChallengeAmount;
use Modules\Brokers\Models\ChallengeCategory;
use Modules\Brokers\Models\ChallengeMatrixValue;
use Modules\Brokers\Models\ChallengeStep;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Repositories\ChallengeAmountRepository;
use Modules\Brokers\Repositories\ChallengeCategoryRepository;
use Modules\Brokers\Repositories\ChallengeStepRepository;
use App\Exceptions\ApiException;

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
     *
     * @return ?ChallengeCategory
     */
    public function getChallengeCategoryByIdAndBroker(int $id, int $broker_id): ?ChallengeCategory
    {
        return $this->repository->findByIdWithoutRelations($id, $broker_id);
    }

    /**
     * Not used-to be deleted
     * Create new challenge category
     */
    // public function createChallengeCategory(array $data): ChallengeCategory
    // {
    //     return DB::transaction(function () use ($data) {
    //         try {
    //             // Validate data
    //             $validatedData = $this->validateChallengeCategoryData($data);

    //             // Create challenge category
    //             $challengeCategory = $this->repository->create($validatedData);

    //             return $challengeCategory->load(['steps', 'amounts']);
    //         } catch (\Exception $e) {
    //             throw new ApiException('Failed to create challenge category', 500, errors: [$e->getMessage()]);
    //         }
    //     });
    // }

    /**
     * Not used-to be deleted
     * Update challenge category
     */
    // public function updateChallengeCategory(int $id, array $data): ChallengeCategory
    // {
    //     return DB::transaction(function () use ($id, $data) {
    //         try {
    //             $challengeCategory = $this->repository->findByIdWithoutRelations($id);

    //             if (! $challengeCategory) {
    //                 throw new \Exception('Challenge category not found');
    //             }

    //             // Validate data
    //             $validatedData = $this->validateChallengeCategoryData($data, true);

    //             // Update challenge category
    //             $this->repository->update($challengeCategory, $validatedData);

    //             return $challengeCategory->load(['steps', 'amounts']);
    //         } catch (\Exception $e) {

    //             throw new ApiException('Failed to update challenge category', 500, errors: [$e->getMessage()]);
    //         }
    //     });
    // }

    /**
     * Delete challenge category
     */
    public function deleteChallengeCategory(int $id, ?int $broker_id = null): void
    {

        $challengeCategory = $this->repository->findByIdWithoutRelations($id, $broker_id);

        if (! $challengeCategory) {
            throw new ApiException('Challenge category not found', 404);
        }

        DB::transaction(function () use ($challengeCategory) {

            //delete challenges and matrix related data
            $challenges = $challengeCategory->challenges()->get();


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
        });
    }

    public function deleteChallengeCategoryStep(int $id, ?int $broker_id = null): void
    {
        $challengeStep = $this->challengeStepRepository->findById($id, $broker_id);
        if (! $challengeStep) {
            throw new ApiException('Challenge step not found', 404);
        }

        DB::transaction(function () use ($challengeStep) {

            //delete challenges and matrix related data
            $challenges = $challengeStep->challenges()->get();
            //
            ChallengeMatrixValue::whereIn('challenge_id', $challenges->pluck('id'))->delete();
            Url::whereIn('urlable_id', $challenges->pluck('id'))
                ->where('urlable_type', Challenge::class)
                ->delete();
            Challenge::whereIn('id', $challenges->pluck('id'))->delete();

            $challengeStep->delete();
        });
    }

    public function deleteChallengeCategoryAmount(int $id, ?int $broker_id = null): void
    {

        $challengeAmount = $this->challengeAmountRepository->findById($id, $broker_id);
        if (! $challengeAmount) {
            throw new ApiException('Challenge amount not found', 404);
        }

        DB::transaction(function () use ($challengeAmount) {
            //delete challenges and matrix related data
            $challenges = $challengeAmount->challenges()->get();
            ChallengeMatrixValue::whereIn('challenge_id', $challenges->pluck('id'))->delete();
            Url::whereIn('urlable_id', $challenges->pluck('id'))
                ->where('urlable_type', Challenge::class)
                ->delete();
            Challenge::whereIn('id', $challenges->pluck('id'))->delete();

            $challengeAmount->delete();
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
     * @param  array<int,int>  $tabIds
     */
    public function saveChallengeTabOrder(array $tabIds, int $brokerId, ChallengeTabEnum|string $tabType): void
    {
        if (empty($tabIds)) {
            return;
        }
        $tabTypeEnum = $tabType instanceof ChallengeTabEnum ? $tabType : ChallengeTabEnum::from($tabType);
        // Normalize and deduplicate while preserving order
        $orderedIds = array_values(array_unique(array_map('intval', $tabIds)));

        DB::transaction(function () use ($orderedIds, $brokerId, $tabTypeEnum) {

            if ($tabTypeEnum === ChallengeTabEnum::CATEGORY) {
                $existingRows = ChallengeCategory::query()
                    ->whereIn('id', $orderedIds)
                    ->where('broker_id', $brokerId)
                    ->get(['id', 'name', 'slug'])
                    ->keyBy('id');
                $rows = [];
                foreach ($orderedIds as $position => $id) {
                    $row = $existingRows->get($id);
                    if (! $row) {
                        throw new ApiException("Category $id not found for broker $brokerId");
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
                    if (! $row) {
                        throw new ApiException("Step $id not found for broker $brokerId");
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
                    if (! $row) {
                        throw new ApiException("Amount $id not found for broker $brokerId");
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
        });
    }

    /**
     * Not used-to be deleted
     * Validate challenge category data
     */
    // public function validateChallengeCategoryData(array $data, bool $isUpdate = false): array
    // {
    //     $rules = [
    //         'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'is_active' => 'boolean',
    //     ];

    //     $validator = Validator::make($data, $rules);

    //     if ($validator->fails()) {
    //         throw new ApiException($validator->errors()->first(), 400);
    //     }

    //     return $validator->validated();
    // }

    /**
     * Add a challenge tab,i.e category, step, amount
     * clone a default tab by id to a broker challenge tab
     */
    public function addChallengeTabToBroker(
        ChallengeTabEnum $tab_type,
        int $default_tab_id_to_clone,
        int $tab_order,
        int $broker_id,
        ?int $broker_challenge_category_id = null,
        ?string $amount_currency = null
    ): ChallengeCategory|ChallengeStep|ChallengeAmount {

        if ($tab_type !== ChallengeTabEnum::CATEGORY && $broker_challenge_category_id === null) {
            throw new ApiException('Broker challenge category id is required for step and amount tabs', 422);
        }

        return match ($tab_type) {
            ChallengeTabEnum::CATEGORY => $this->repository->cloneCategory($default_tab_id_to_clone, $tab_order, $broker_id),
            ChallengeTabEnum::STEP => $this->challengeStepRepository->cloneStep($default_tab_id_to_clone, $tab_order, $broker_challenge_category_id),
            ChallengeTabEnum::AMOUNT => $this->challengeAmountRepository->cloneAmount($default_tab_id_to_clone, $tab_order, $broker_challenge_category_id, $amount_currency),
        };
    }
}
