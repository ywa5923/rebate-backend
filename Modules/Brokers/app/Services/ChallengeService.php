<?php

namespace Modules\Brokers\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\Challenge;
use Modules\Brokers\Repositories\ChallengeAmountRepository;
use Modules\Brokers\Repositories\ChallengeMatrixRepository;
use Modules\Brokers\Repositories\ChallengeRepository;
use Modules\Brokers\Repositories\CostDiscountRepository;
use Modules\Brokers\Repositories\UrlRepository;
use Modules\Brokers\Transformers\AffiliateLinkResource;
use Modules\Brokers\Transformers\CostDiscountResource;
use App\Exceptions\ApiException;
use Modules\Brokers\DTOs\ChallengeMatrixCellDTO;
class ChallengeService
{
    public function __construct(
        protected ChallengeRepository $challengeRepository,
        protected UrlRepository $urlRepository,
        protected CostDiscountRepository $costDiscountRepository,
        protected ChallengeMatrixRepository $challengeMatrixRepository,
        protected ChallengeAmountRepository $challengeAmountRepository
    ) {
    }

    
    /**
     * Store challenge with matrix data
     */
    public function processRequest(array $validatedData, ?int $brokerId, bool $isPlaceholder, bool $isAdmin, ?int $zoneId = null): array
    {

        return DB::transaction(function () use ($validatedData, $brokerId, $isPlaceholder, $isAdmin, $zoneId) {
            //check if challenge already exists
            //if it exist delete it
            $challenge = $this->challengeRepository->exists((int) $isPlaceholder, $validatedData['category_id'], $validatedData['step_id'], $validatedData['amount_id'] ?? null, $brokerId, $zoneId);

           
            if (! $challenge) {

                //create a new challenge if it does not exist even in placeholder mode
                //if it exists then update it
                $challenge = $this->challengeRepository->create([
                    'is_placeholder' => $isPlaceholder,
                    'challenge_category_id' => $validatedData['category_id'],
                    'challenge_step_id' => $validatedData['step_id'],
                    'challenge_amount_id' => $validatedData['amount_id'] ?? null,
                    'broker_id' => $brokerId,
                    'zone_id' => $zoneId,

                ]);
                // Save matrix data
                //for placeholder mode, placeholder texts are stored in public_value
                $this->saveMatrixData($isPlaceholder, $validatedData['matrix'], $challenge->id, $brokerId, $isAdmin,$zoneId);

                $this->saveNewMatrixExtraData($validatedData, $challenge->id, $brokerId, $isPlaceholder, $isAdmin, $zoneId);
            } else {
                //if a challenge exist for this matrix,udate,delete or create the matrix and the extra data

                //how matrix save works in frontend
                //1. if the admin is true and the matrix cell public_value is empty,
                //the cell's value will be injected in matrix's cell's public_value
                //2. In the placeholder mode ,is_placeholder is true, the matrix works like for the user,i.e, the cell's values will not be injected in matrix's cell's public_values
                //3. For matrix extradata, if the admin is true and the extra data public_value is empty,
                //the extra data's value will be injected in extra data's public_value
                //4. In the placeholder mode ,is_placeholder is true, the extra data works like for the user,i.e, the extra data's value will not be injected in extra data's public_value
                //5. When the matrix is saved from the frontend, the matrix data is sent
                //to the backend as the new matrix data containing cell's public_values,and values.
                //6. When the extra data is saved from the frontend, the extra data data is sent
                //to the backend : for admin will be sent only public_values of the links and discounts, for placeholder and user will be sent only their values.

                //if a user is updating the matrix, set cell's previous_value to the cell's value

                
                //the admin update public values of the matrix cells, the user update the values,in placeholder mode the values are updated
                $this->updateMatrix($isPlaceholder, $validatedData['matrix'], $challenge->id, $brokerId, $isAdmin,$zoneId);

                //Compare and update the existing matrix and extra data(affiliate link, affiliate master link, evaluation cost discount)
                $this->updateMatrixAndExtraData($validatedData, $challenge->id, $brokerId, $isPlaceholder, $isAdmin, $zoneId);
            }

            return ['challenge_id' => $challenge->id];
        });
        
    }

    public function updateMatrix(bool $isPlaceholder, array $newMatrix, int $challengeId, ?int $brokerId,  ?bool $isAdmin = null, ?int $zoneId = null): void
    {
        foreach($newMatrix as $rowIndex => $rowData){
            foreach($rowData as $colIndex => $cellData){
                $cellDTO = ChallengeMatrixCellDTO::fromValidated($cellData);
                $this->challengeMatrixRepository->updateChallengeMatrixValue($isPlaceholder, $cellDTO,$challengeId, $brokerId, $isAdmin, $zoneId);
            }
        }
    }

    public function updateMatrixAndExtraData(array $validatedData, int $challengeId, ?int $brokerId, bool $isPlaceholder, bool $isAdmin, ?int $zoneId = null): void
    {
        $this->costDiscountRepository->upsertCostDiscount($challengeId, $validatedData['evaluation_cost_discount'] ?? null, $brokerId, $isAdmin, $isPlaceholder, $zoneId);
        $this->urlRepository->upsertAffiliateLink($challengeId, $validatedData['affiliate_link'] ?? null, 'Affiliate Link', $brokerId, $isAdmin, $isPlaceholder, $zoneId);
        $this->urlRepository->upsertAffiliateLink(null, $validatedData['affiliate_master_link'] ?? null, 'Affiliate Master Link', $brokerId, $isAdmin, $isPlaceholder, $zoneId);
    }

    /**
     * Save new matrix extra data:affiliate link, affiliate master link, evaluation cost discount
     *
     * @throws \Exception
     */
    public function saveNewMatrixExtraData(array $validatedData, int $challengeId, ?int $brokerId, bool $isPlaceholder, ?bool $isAdmin = null, ?int $zoneId = null): void
    {
       
        //broker id is nullable for placeholder data
        if (! empty($validatedData['affiliate_link'])) {
            $this->urlRepository->saveAffiliateLink(
                $challengeId,
                $validatedData['affiliate_link'],
                'Affiliate Link',
                $brokerId,
                $isAdmin,
                $isPlaceholder,
                $zoneId,
            );
        }

        if (! empty($validatedData['affiliate_master_link'])) {
            $this->urlRepository->upsertAffiliateLink(
                null,
                $validatedData['affiliate_master_link'],
                'Affiliate Master Link',
                $brokerId,
                $isAdmin,
                $isPlaceholder,
                $zoneId,
            );
        }
        //save the evaluation cost discount
        if (! empty($validatedData['evaluation_cost_discount'])) {
            $this->costDiscountRepository->createCostDiscount(
                $challengeId,
                $validatedData['evaluation_cost_discount'],
                $brokerId,
                $isAdmin,
                $isPlaceholder,
                $zoneId,
            );
        }
    }

    /**
     * Save matrix data to challenge_matrix_values table
     */
    private function saveMatrixData(bool $isPlaceholder, array $matrixData, int $challengeId, ?int $brokerId, ?bool $isAdmin = null,?int $zoneId = null ): void
    {
        $challengeMatrixValues = [];
        $groupNames = ['challenge', 'step-0', 'step-1', 'step-2'];

        // Fetch all headers for the needed groups once and index by slug
        $headersBySlug = $this->challengeRepository
            ->getMatrixHeadersByGroups($groupNames)
            ->keyBy('slug');

        //dd($matrixData);
        foreach ($matrixData as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $cellData) {
                $rowHeaderSlug = $cellData['row_slug'];
                $colHeaderSlug = $cellData['col_slug'];

                // Get matrix headers
                $rowHeader = $headersBySlug->get($rowHeaderSlug);
                $colHeader = $headersBySlug->get($colHeaderSlug);

                if (! $rowHeader || ! $colHeader) {
                    throw new ApiException('Row or column header not found for: '.$rowHeaderSlug.' or '.$colHeaderSlug, 404);
                }

                $challengeMatrixValues[] = [
                    'previous_value' => $cellData['previous_value']??null,
                    //'value' => ($isAdmin && ! $isPlaceholder) ? $cellData['public_value']??null : $cellData['value']??null,
                    'value' => $cellData['value']??null,
                    'public_value' => $cellData['public_value']??null,
                    'is_updated_entry' => 0,
                    'zone_id' => $zoneId,
                    'challenge_id' => $challengeId,
                    'row_id' => $rowHeader->id,
                    'column_id' => $colHeader->id,
                    'broker_id' => $brokerId,
                ];
            }
        }

        $this->challengeRepository->insertChallengeMatrixValues($challengeMatrixValues);
    }

    /**
     * Find url by urlable type and id
     *
     * @param  bool|null  $isPlaceholder
     */
    public function findUrlByUrlableTypeAndId(string $urlableType, ?int $urlableId, ?int $brokerId, bool $isPlaceholder = false, ?int $zoneId = null): ?AffiliateLinkResource
    {
        $chalengeObject = $this->urlRepository->findByUrlableTypeAndId($urlableType, $urlableId, $brokerId, $isPlaceholder, $zoneId);
        if ($chalengeObject) {
            return AffiliateLinkResource::make($chalengeObject);
        }

        return null;
    }

    public function findDiscountByChallengeId(int $challengeId, ?int $brokerId): ?CostDiscountResource
    {
        $discountObject = $this->costDiscountRepository->findByChallengeId($challengeId, $brokerId);

        if ($discountObject) {
            return CostDiscountResource::make($discountObject);
        }

        return null;
    }

    /**
     * Get challenge by ID
     */
    public function getChallenge(int $id): ?Challenge
    {
        return $this->challengeRepository->findById($id);
    }

    /**
     * Find challenge by parameters
     */
    public function findChallengeByParams(bool $isPlaceholder, int $categoryId, int $stepId, ?int $amountId, ?int $brokerId = null, ?int $zoneId = null): ?Challenge
    {

        return $this->challengeRepository->exists($isPlaceholder, $categoryId, $stepId, $amountId, $brokerId, $zoneId);
    }

    /**
     * Get challenge matrix values
     */
    public function getChallengeMatrixValues(int $challengeId)
    {
        return $this->challengeRepository->getChallengeMatrixValues($challengeId);
    }

    /**
     * Get challenge matrix data in the required format
     */
    public function getChallengeMatrixData(int $challengeId, ?int $zoneId = null): array
    {
        $matrixValues = $this->challengeRepository->getChallengeMatrixValues($challengeId, $zoneId);

        if ($matrixValues->isEmpty()) {
            return [];
        }

        // Group by row slug
        $groupedByRow = $matrixValues->groupBy('row.slug');

        $matrix = [];
        $rowIndex = 0;

        foreach ($groupedByRow as $rowSlug => $rowValues) {
            $row = [];

            foreach ($rowValues as $value) {
                $row[] = [
                    'id' => $value->id,
                    'previous_value' => $value->previous_value,
                    'previous_public_value' => $value->previous_public_value,
                    'value' => $value->value,
                    'public_value' => $value->public_value,
                    'is_updated_entry' => $value->is_updated_entry,
                    'row_slug' => $value->row->slug,
                    'col_slug' => $value->column->slug,
                    'zone_id' => $value->zone_id,
                    'row_id' => $value->row_id,
                    'column_id' => $value->column_id,
                    'created_at' => $value->created_at,
                    'updated_at' => $value->updated_at,
                   
                ];
            }

            $matrix[$rowIndex] = $row;
            $rowIndex++;
        }

        return $matrix;
    }

    /**
     * Merge previous matrix values into the new matrix payload.
     * Sets previous_value (and preserves is_updated_entry if present).
     *
     * @param  array  $previousMatrix  Matrix returned from getChallengeMatrixData
     * @param  array  $newMatrix  Incoming matrix from client payload
     * @return array Updated matrix with previous_value filled where applicable
     */
    public function setPreviousValueInMatrixData(array $previousMatrix, array $newMatrix): array
    {
        $previousByKey = [];
        foreach ($previousMatrix as $row) {
            foreach ($row as $cell) {
                $key = ($cell['rowHeader'] ?? '').'|'.($cell['colHeader'] ?? '');
                $previousByKey[$key] = [
                    'value' => $cell['value'] ?? null,
                    'previous_value' => $cell['previous_value'] ?? null,
                    /// 'is_updated_entry' => $cell['is_updated_entry'] ?? 0,
                    //NEW
                    'is_updated_entry' => $cell['is_updated_entry'] ?? 0,
                ];
            }
        }

        foreach ($newMatrix as &$row) {
            foreach ($row as &$cell) {
                $key = ($cell['rowHeader'] ?? '').'|'.($cell['colHeader'] ?? '');
                if (isset($previousByKey[$key])) {
                    $previousValue = $previousByKey[$key]['value'];
                    $currentValue = $cell['value'];
                    if( $previousValue != $currentValue){
                        $cell['previous_value'] = $previousValue.'->'.$previousByKey[$key]['previous_value'];
                        $cell['is_updated_entry'] = true;
                    }
                  
                    if ($previousByKey[$key]['is_updated_entry']) {
                        $cell['is_updated_entry'] = true;
                    }
                }
            }
        }
        unset($row, $cell);

        return $newMatrix;
    }

    /**
     * Check if challenge exists
     */
    public function challengeExist(
        bool $isPlaceholder,
        int $categoryId,
        int $stepId,
        ?int $amountId,
        int $brokerId
    ): ?Challenge {
        return $this->challengeRepository->exists($isPlaceholder, $categoryId, $stepId, $amountId, $brokerId);
    }

    /**
     * =======Deprecated method=================
     * Handle placeholder challenge request
     */
    public function handlePlaceholderRequest(array $validatedData): array
    {
        $brokerId = $validatedData['broker_id'] ?? null;
        $zoneId = $validatedData['zone_id'] ?? null;
        $categoryId = $validatedData['category_id'];
        $stepId = $validatedData['step_id'];

        $placeholderChallenge = $this->findChallengeByParams(
            true,
            $categoryId,
            $stepId,
            null,
            $brokerId,
            $zoneId
        );

        if ($placeholderChallenge?->id) {
            return [
                'success' => true,
                'data' => [
                    'challenge_id' => $placeholderChallenge->id,
                    'matrix' => $this->getChallengeMatrixData($placeholderChallenge->id),
                    'evaluation_cost_discount' => $this->findDiscountByChallengeId($placeholderChallenge->id, $brokerId),
                    'affiliate_link' => $this->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, true, $zoneId),
                    'affiliate_master_link' => $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId),
                ],
            ];
        }

        return [
            'success' => true,
            'message' => 'Placeholder challenge not found, return only the affiliate master link placeholder',
            'data' => [
                'affiliate_master_link' => $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId),
            ],
        ];
    }

    /**
     * Add placeholder data if needed
     *
     */
    public function addPlaceholderData(array &$responseArray, int $brokerId, int $brokerCategoryId, int $brokerStepId, ?int $zoneId): void
    {
        //#When the admin save a challenge matrix data for placeholders, the challenge category id and step id are
        //the ones defined by admin which have broker_id=null.
        //#Every broker has its own challenge categories and steps, so here $categoryId and $stepId are the ones cloned for the broker at registration time.
        //#To get the placeholder challenge row from challenges table, we need to get the   category id and step id which are defined by admin with broker_id=null
        //1.In repo, get the challenge category id by slug ,then search by that slug the category id with broker_id=null
        //2. Same logic for challenge steps within one query and inner join
        //So originalCatId and originalStepId are the ones defined by admin with broker_id=null that have same slugs with the ones cloned for the broker at registration time.

        //defaultCatId and defaultStepId are the ones defined by admin with broker_id=null that have same slugs with the ones cloned for the broker at registration time.
        $defaultCatId = $this->challengeRepository->getPlaceholderCategoryId($brokerCategoryId, $brokerId);
        $defaultStepId = $this->challengeRepository->getPlaceholderStepId($brokerStepId,$defaultCatId);

        if (! $defaultCatId || ! $defaultStepId) {
            throw new \Exception('Original Category id or step id not found');
        }

        $placeholderChallenge = $this->findChallengeByParams(
            true,
            $defaultCatId,
            $defaultStepId,
            ChallengeRepository::AMOUNT_ID_NULL,
            ChallengeRepository::BROKER_ID_NULL,
            $zoneId
        );

        if ( !$placeholderChallenge?->id) {
            return;
          
        }

        $responseArray['matrix_placeholders_array'] = $this->getMatrixPlaceholderArray($placeholderChallenge->id);
        $responseArray['evaluation_cost_discount_placeholder'] = $this->findDiscountByChallengeId($placeholderChallenge->id,null)?->value;
        $responseArray['affiliate_link_placeholder'] = $this->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, null, true, $zoneId)?->url;
        $responseArray['affiliate_master_link_placeholder'] = $this->findUrlByUrlableTypeAndId(Challenge::class, null, null, true, $zoneId)?->url;
    }

    /**
     * Check if matrix has empty cells
     */
    public function hasEmptyMatrixCells(array $matrix): bool
    {
        foreach ($matrix as $row) {
            foreach ($row as $cell) {
                if (empty($cell['value']['text']) || (is_array($cell['value']['text']) && empty(array_filter($cell['value']['text'])))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get matrix placeholder array
     * Compares the placeholder matrix with the challenge matrix and returns the placeholders cells array for each empty cell in the challenge matrix
     */
    public function getMatrixPlaceholderArray(int $placeholderChallengeId, ?int $challengeId = null): array
    {
        $placeholderMatrix = $this->getChallengeMatrixData($placeholderChallengeId);
        //$challengeMatrix = ($challengeId) ? $this->getChallengeMatrixData($challengeId) : null;

        return $this->extractPlaceholderValuesFromMatrix($placeholderMatrix);
    }

    /**
     * Get placeholders cells array
     * Compares the placeholder matrix with the challenge matrix and returns the placeholders cells array for each empty cell in the challenge matrix
     */
    private function extractPlaceholderValuesFromMatrix(array $placeholderMatrix, ?array $matrix = null): array
    {
        $placeholders = [];
        if ($matrix) {
            foreach ($matrix as $rowIndex => $row) {
                foreach ($row as $colIndex => $cell) {
                    if (empty($cell['value']) ) {
                        $placeholders[$cell['row_slug'].'-'.$cell['col_slug']] = $placeholderMatrix[$rowIndex][$colIndex]['value'];
                    }
                }
            }
        } else {
            foreach ($placeholderMatrix as $rowIndex => $row) {
                foreach ($row as $colIndex => $cell) {
                    $placeholders[$cell['row_slug'].'-'.$cell['col_slug']] = $cell['value'];
                }
            }
        }

        return $placeholders;
    }

    public function getChallengeData(?int $chId, ?int $brokerId, bool $isPlaceholder, ?int $zoneId): array
    {
        //for plceholder data $brokerId is null
        $matrix = $chId ? $this->getChallengeMatrixData($chId) : null;
        $discount = $chId ? $this->findDiscountByChallengeId($chId, $brokerId) : null;
        $affiliateLink = $chId ? $this->findUrlByUrlableTypeAndId(Challenge::class, $chId, $brokerId, $isPlaceholder, $zoneId) : null;
        $affiliateMasterLink = $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, $isPlaceholder, $zoneId);

        return [
            'challenge_id' => $chId,
            'matrix' => $matrix,
            'evaluation_cost_discount' => $discount,
            'affiliate_link' => $affiliateLink,
            'affiliate_master_link' => $affiliateMasterLink,
        ];
    }

    /**
     * Clone default challenges to broker
     */
    public function cloneDefaultChallengesToBroker(int $brokerId): array
    {

        $now = now();

        // 1) Fetch global categories (templates)
        $defaultCategories = DB::table('challenge_categories')
            ->whereNull('broker_id')
            ->get(['id', 'name', 'description', 'image', 'slug']);

        if ($defaultCategories->isEmpty()) {
            return ['categories' => 0, 'steps' => 0, 'amounts' => 0];
        }

        // 2) Insert broker categories, keep old->new id map
        $catRows = [];
        foreach ($defaultCategories as $cat) {
            $catRows[] = [
                'name' => $cat->name,
                'description' => $cat->description,
                'image' => $cat->image,
                // ensure slug uniqueness per broker (adjust if you already enforce unique)
                'slug' => $cat->slug, // or "{$cat->slug}-b{$brokerId}"
                'broker_id' => $brokerId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('challenge_categories')->insert($catRows);

        $newCategories = DB::table('challenge_categories')
            ->where('broker_id', $brokerId)
            ->whereIn('slug', $defaultCategories->pluck('slug'))
            ->get(['id', 'slug']);

        // Map by slug (or map by (name, slug) if needed)
        $slugToNewId = $newCategories->pluck('id', 'slug');

        // Build oldId -> newId map
        $oldToNewCatId = [];
        foreach ($defaultCategories as $cat) {
            $newId = $slugToNewId[$cat->slug] ?? null;
            if ($newId) {
                $oldToNewCatId[$cat->id] = $newId;
            }
        }

        // 3) Clone steps
        $defaultSteps = DB::table('challenge_steps')
            ->whereIn('challenge_category_id', array_keys($oldToNewCatId))
            ->get(['id', 'name', 'description', 'image', 'slug', 'challenge_category_id']);

        $stepRows = [];
        foreach ($defaultSteps as $s) {
            $stepRows[] = [
                'name' => $s->name,
                'description' => $s->description,
                'image' => $s->image,
                'slug' => $s->slug,
                'challenge_category_id' => $oldToNewCatId[$s->challenge_category_id] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if (! empty($stepRows)) {
            DB::table('challenge_steps')->insert($stepRows);
        }

        // 4) Clone amounts
        $defaultAmounts = DB::table('challenge_amounts')
            ->whereIn('challenge_category_id', array_keys($oldToNewCatId))
            ->get(['amount', 'currency', 'challenge_category_id']);

        $amountRows = [];
        foreach ($defaultAmounts as $a) {
            $amountRows[] = [
                'amount' => $a->amount,
                'currency' => $a->currency,
                'challenge_category_id' => $oldToNewCatId[$a->challenge_category_id] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if (! empty($amountRows)) {
            DB::table('challenge_amounts')->insert($amountRows);
        }

        return [
            'categories' => count($catRows),
            'steps' => count($stepRows),
            'amounts' => count($amountRows),
        ];
    }

    /**
     * Set publish state for a non-placeholder challenge identified by tuple.
     * Uses pessimistic locking to avoid races.
     *
     * @return bool Final published state
     */
    public function toggleChallengePublish(bool $isPublished, int $categoryId, int $stepId, int $amountId, int $brokerId, ?int $zoneId = null): bool
    {
        return DB::transaction(function () use ($categoryId, $stepId, $amountId, $brokerId, $zoneId, $isPublished) {
            $query = Challenge::where([
                'is_placeholder' => false,
                'challenge_category_id' => $categoryId,
                'challenge_step_id' => $stepId,
                'challenge_amount_id' => $amountId,
                'broker_id' => $brokerId,
            ]);
            if ($zoneId !== null) {
                $query->where('zone_id', $zoneId);
            } else {
                $query->whereNull('zone_id');
            }

            // Lock the row to avoid race conditions
            $challenge = $query->lockForUpdate()->first();
            if (! $challenge) {
                throw new \RuntimeException('Challenge not found');
            }

            $challenge->is_published = $isPublished;
            $challenge->save();

            return true;
        });
    }

    public function cloneChallengeMatrix(int $categoryId, int $stepId, int $amountId, int $brokerId, bool $isAdmin, ?int $zoneId = null): void
    {
        $chalenge = $this->challengeRepository->exists(false, $categoryId, $stepId, $amountId, $brokerId, $zoneId);
        if (! $chalenge) {
            throw new ApiException('Challenge not found', 404);
        }
        //get all user amounts except the given amount id
        $amountsToClone = $this->challengeAmountRepository->getUserAmountsExcept($brokerId, $amountId, $categoryId);

        if (empty($amountsToClone)) {
            throw new ApiException('Amounts to clone not found', 404);
        }

        DB::transaction(function () use ($categoryId, $stepId, $amountsToClone, $brokerId, $zoneId, $chalenge, $isAdmin) {

            //get new challenges to clone, if a challenge already exists for the user for amount_id in $amountsToClone,
            // it will not be created again
            $newChallengeIds = $this->challengeRepository->syncChallengesForAmounts($amountsToClone,$chalenge->is_published, $categoryId, $stepId,  $brokerId, $zoneId);

            if (! empty($newChallengeIds)) {
                //clone matrix values for the new challenges
                //if matrix values already exist for the new challenges, they will be deleted and then cloned with the new values from the original challenge

                $this->challengeMatrixRepository->clone($chalenge->id, $newChallengeIds, $brokerId, $isAdmin, $zoneId);
                //clone cost discounts for the new challenges
                $this->costDiscountRepository->cloneCostDiscounts($chalenge->id, $newChallengeIds, $brokerId, $isAdmin, $zoneId);

                //clone affiliate links for the new challenges
                $this->urlRepository->cloneChallengeAffiliateLinks($chalenge->id, $newChallengeIds, $brokerId, $isAdmin, $zoneId);

            }

        });
    }
}
