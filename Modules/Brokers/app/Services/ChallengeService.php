<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\ChallengeRepository;
use Modules\Brokers\Models\Challenge;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\Brokers\Repositories\UrlRepository;
use Modules\Brokers\Repositories\CostDiscountRepository;
use Modules\Brokers\Models\CostDiscount;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Transformers\CostDiscountResource;
use Modules\Brokers\Transformers\AffiliateLinkResource;


class ChallengeService
{
    protected ChallengeRepository $challengeRepository;
    protected UrlRepository $urlRepository;
    protected CostDiscountRepository $costDiscountRepository;

    public function __construct(ChallengeRepository $challengeRepository, UrlRepository $urlRepository, CostDiscountRepository $costDiscountRepository)
    {
        $this->challengeRepository = $challengeRepository;
        $this->urlRepository = $urlRepository;
        $this->costDiscountRepository = $costDiscountRepository;
    }


    /**
     * Validate post request data recive in ChallengeController::store method
     * @param Request $request
     * @return array
     */
    public function validatePostRequestData(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            'step_slug' => 'nullable|string',
            'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
            'is_placeholder' => 'nullable|boolean',
            'matrix' => 'required|array',
            'broker_id' => 'nullable|integer|exists:brokers,id',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
            'is_admin' => 'sometimes|nullable|boolean',
            'evaluation_cost_discount' => 'sometimes|nullable|string',
            'affiliate_link' => 'sometimes|nullable|string',
            'affiliate_master_link' => 'sometimes|nullable|string',
        ]);
    }

    /**
     * Validate get request data recive in ChallengeController::show method
     * @param Request $request
     * @return array
     */
    public function validateGetRequestData(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
            'is_placeholder' => 'nullable|boolean',
            'broker_id' => 'nullable|integer|exists:brokers,id',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
        ]);
    }

    /**
     * Store challenge with matrix data
     */
    public function processRequest(array $validatedData, int $brokerId, bool $isPlaceholder, bool $isAdmin, ?int $zoneId = null): array
    {

        $brokerId = $validatedData['broker_id'];
        $zoneId = $validatedData['zone_id'] ?? null;
       // $isAdmin = $validatedData['is_admin'] ?? null;
       // $isPlaceholder = $validatedData['is_placeholder'];

        DB::beginTransaction();

        try {
            //check if challenge already exists
            //if it exist delete it
            $challenge = $this->challengeRepository->exists((int)$validatedData['is_placeholder'], $validatedData['category_id'], $validatedData['step_id'], $validatedData['amount_id'] ?? null, $brokerId);
            
            if (!$challenge) {

                //create a new challenge if it does not exist even in placeholder mode
                //if it exists then update it
                $challenge = $this->challengeRepository->create([
                    'is_placeholder' => $isPlaceholder,
                    'challenge_category_id' => $validatedData['category_id'],
                    'challenge_step_id' => $validatedData['step_id'],
                    'challenge_amount_id' => $validatedData['amount_id'] ?? null,
                    'broker_id' => $brokerId,

                ]);
                // Save matrix data
                $this->saveMatrixData($validatedData['matrix'], $challenge->id, $brokerId, $zoneId, $isAdmin);

                $this->saveNewMatrixExtraData($validatedData, $challenge->id, $brokerId, $isPlaceholder, $isAdmin,$zoneId );


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
                $newMatrix = ($isPlaceholder || $isAdmin) ? $validatedData['matrix'] : $this->setPreviousValueInMatrixData(
                    $this->getChallengeMatrixData($challenge->id, $zoneId),
                    $validatedData['matrix'],
                );
                //remove old chalenge's matrix values
                $this->challengeRepository->deleteChallengeMatrixValues($challenge->id, $zoneId); // Save matrix data
               //save the new matrix data
                $this->saveMatrixData($newMatrix, $challenge->id, $brokerId, $zoneId, $isAdmin);
             
                //Compare and update the existing matrix and extra data(affiliate link, affiliate master link, evaluation cost discount)
                $this->updateMatrixAndExtraData($validatedData, $challenge->id, $brokerId, $isPlaceholder, $isAdmin, $zoneId);
                
            }

            // Create challenge
            DB::commit();

            return [
                'success' => true,
                'challenge_id' => $challenge->id
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    public function updateMatrixAndExtraData(array $validatedData, int $challengeId, int $brokerId, bool $isPlaceholder,bool $isAdmin, ?int $zoneId = null): void
    {
        $this->costDiscountRepository->upsertCostDiscount($challengeId, $validatedData['evaluation_cost_discount']??null, $brokerId, $isAdmin, $isPlaceholder, $zoneId);
        $this->urlRepository->upsertAffiliateLink($challengeId, $validatedData['affiliate_link']??null, 'Affiliate Link', $brokerId, $isAdmin, $isPlaceholder, $zoneId);
        $this->urlRepository->upsertAffiliateLink(null, $validatedData['affiliate_master_link']??null, 'Affiliate Master Link', $brokerId, $isAdmin, $isPlaceholder, $zoneId);
    }
    /**
     * =========== DEPRECATED FUNCTION =================
     * Update matrix and extra data:affiliate link, affiliate master link, evaluation cost discount
     * @param array $validatedData
     * @param int $challengeId
     * @param int $brokerId
     * @param bool $isPlaceholder
     * @param bool $isAdmin
     * @param int|null $zoneId
     * @return void
     * @throws \Exception
     */
    public function updateMatrixAndExtraData2(array $validatedData, int $challengeId, int $brokerId, bool $isPlaceholder,bool $isAdmin, ?int $zoneId = null): void
    {
        $oldCostDiscount = $this->costDiscountRepository->findByChallengeId($challengeId, $brokerId, $zoneId);
       
        $oldAffiliateLink = $this->urlRepository->findByUrlableTypeAndId(Challenge::class, $challengeId, $brokerId, $isPlaceholder, $zoneId);
        $oldAffiliateMasterLink = $this->urlRepository->findByUrlableTypeAndId( Challenge::class, null, $brokerId, $isPlaceholder, $zoneId);
           
    
        //update the evaluation cost discount
        if ($oldCostDiscount) {
            //if old evaluation cost discount is found, update it
            $oldDiscountValue = $isAdmin ? $oldCostDiscount->public_value : $oldCostDiscount->value;
            
            $newDiscountValue = $validatedData['evaluation_cost_discount']??null;
            if ( $oldDiscountValue != $newDiscountValue && !is_null($newDiscountValue)) {
                $oldCostDiscount->update([
                    ($isAdmin || $isPlaceholder) ? null : 'previous_value' => $oldDiscountValue,
                    $isAdmin && !$isPlaceholder ? 'public_value' : 'value' => $newDiscountValue,
                    'is_updated_entry' => $isAdmin ? false : true,
                ]);
            }elseif(is_null($newDiscountValue)){
                $oldCostDiscount->delete();
            }
        } else {
            //if the old evaluation cost discount is not found, create a new one
            if (!empty($validatedData['evaluation_cost_discount'])) {
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
        if ($oldAffiliateLink) {
            $oldAffiliateLinkValue = $isAdmin ? $oldAffiliateLink->public_url : $oldAffiliateLink->url;
            $newAffiliateLinkValue = $validatedData['affiliate_link']??null;
            if ($oldAffiliateLinkValue !=   $newAffiliateLinkValue && !is_null($newAffiliateLinkValue)) {
                $oldAffiliateLink->update([
                    ($isAdmin || $isPlaceholder) ? null : 'previous_url' => $oldAffiliateLink?->url,
                    $isAdmin && !$isPlaceholder ? 'public_url' : 'url' => $newAffiliateLinkValue,
                    'is_updated_entry' => $isAdmin || $isPlaceholder ? false : true,
                ]);
            }elseif(is_null($newAffiliateLinkValue)){
                $oldAffiliateLink->delete();
            }
        } else {
            //if the old affiliate link is not found, create a new one
            if (!empty($validatedData['affiliate_link'])) {
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
        }

        if ($oldAffiliateMasterLink!=null) {
            $oldAffiliateMasterLinkValue = $isAdmin ? $oldAffiliateMasterLink->public_url : $oldAffiliateMasterLink->url;
            $newAffiliateMasterLinkValue = $validatedData['affiliate_master_link']??null;
           
            if ( trim($oldAffiliateMasterLinkValue) != trim($newAffiliateMasterLinkValue) && !is_null($newAffiliateMasterLinkValue)) {
                $oldAffiliateMasterLink->update([
                    ($isAdmin || $isPlaceholder) ? null : 'previous_url' => $oldAffiliateMasterLink->url,
                    $isAdmin && !$isPlaceholder ? 'public_url' : 'url' => $newAffiliateMasterLinkValue,
                    'is_updated_entry' => ($isAdmin || $isPlaceholder) ? false : true,
                ]);
            }elseif(is_null($newAffiliateMasterLinkValue)){
                $oldAffiliateMasterLink->delete();
            }
        } else {
            //if the old affiliate master link is not found, create a new one
            if (!empty($validatedData['affiliate_master_link'])) {
                $this->urlRepository->saveAffiliateLink(
                    null,
                    $validatedData['affiliate_master_link'],
                    'Affiliate Master Link',
                    $brokerId,
                    $isAdmin,
                    $isPlaceholder,
                    $zoneId,
                );
            }
        }
    }

    /**
     * Save new matrix extra data:affiliate link, affiliate master link, evaluation cost discount
     * @param array $validatedData
     * @param int $challengeId
     * @param int $brokerId
     * @param bool $isPlaceholder
     * @param int|null $zoneId
     * @param bool|null $isAdmin
     * @return void
     * @throws \Exception
     */
    public function saveNewMatrixExtraData(array $validatedData, int $challengeId, int $brokerId,bool $isPlaceholder, ?bool $isAdmin = null, ?int $zoneId = null): void
    {
        if (!empty($validatedData['affiliate_link'])) {
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

        if (!empty($validatedData['affiliate_master_link'])) {
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
         if (!empty($validatedData['evaluation_cost_discount'])) {
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
    private function saveMatrixData(array $matrixData, int $challengeId, int $brokerId, ?int $zoneId = null, ?bool $isAdmin = null): void
    {
        $challengeMatrixValues = [];
        $groupNames = ['challenge', 'step-0', 'step-1', 'step-2'];

        // Fetch all headers for the needed groups once and index by slug
        $headersBySlug = $this->challengeRepository
            ->getMatrixHeadersByGroups($groupNames)
            ->keyBy('slug');

        foreach ($matrixData as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $cellData) {
                $rowHeaderSlug = $cellData['rowHeader'];
                $colHeaderSlug = $cellData['colHeader'];

                // Get matrix headers
                $rowHeader = $headersBySlug->get($rowHeaderSlug);
                $colHeader = $headersBySlug->get($colHeaderSlug);

                if (!$rowHeader || !$colHeader) {
                    throw new \Exception('Row or column header not found for: ' . $rowHeaderSlug . ' or ' . $colHeaderSlug);
                }

                $challengeMatrixValues[] = [
                    'previous_value' => !empty($cellData['previous_value']) ? json_encode($cellData['previous_value']) : null,
                    'value' => !empty($cellData['value']) ? json_encode($cellData['value']) : null,
                    'public_value' => !empty($cellData['public_value']) ? json_encode($cellData['public_value']) : null,
                   // 'is_updated_entry' => $isAdmin ? 0 : (int)($cellData['is_updated_entry'] ?? 0),
                    'is_updated_entry' => (int)$cellData['is_updated_entry'] ?? 0,
                    'is_invariant' => isset($zoneId) ? false : true,
                    'zone_id' => $zoneId,
                    'challenge_id' => $challengeId,
                    'row_id' => $rowHeader->id,
                    'column_id' => $colHeader->id,
                    'broker_id' => $brokerId
                ];
            }
        }

        $this->challengeRepository->insertChallengeMatrixValues($challengeMatrixValues);
    }

    /**
     * Find url by urlable type and id
     * @param string $urlableType
     * @param int|null $urlableId
     * @param int $brokerId
     * @param int|null $zoneId
     * @param bool|null $isPlaceholder
     * @return AffiliateLinkResource|null
     */
    public function findUrlByUrlableTypeAndId(string $urlableType, ?int $urlableId, int $brokerId, bool $isPlaceholder = false, ?int $zoneId = null): ?AffiliateLinkResource
    {
       $chalengeObject=$this->urlRepository->findByUrlableTypeAndId($urlableType, $urlableId, $brokerId, $isPlaceholder, $zoneId);
       if($chalengeObject){
        return AffiliateLinkResource::make($chalengeObject);
       }
       return null;
    }


    public function findDiscountByChallengeId(int $challengeId, int $brokerId, ?int $zoneId = null): ?CostDiscountResource
    {
        $discountObject=$this->costDiscountRepository->findByChallengeId($challengeId, $brokerId, $zoneId);
       //dd($challengeId, $brokerId, $zoneId, $discountObject);
       
        if($discountObject){
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
    public function findChallengeByParams(bool $isPlaceholder, int $categoryId, int $stepId, ?int $amountId, int $brokerId): ?Challenge
    {
        return $this->challengeRepository->exists($isPlaceholder, $categoryId, $stepId, $amountId, $brokerId);
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

        // Group by row headers
        $groupedByRow = $matrixValues->groupBy('row.slug');

        $matrix = [];
        $rowIndex = 0;

        foreach ($groupedByRow as $rowSlug => $rowValues) {
            $row = [];

            foreach ($rowValues as $value) {
                $row[] = [
                    'previous_value' => json_decode($value->previous_value, true) ?: [],
                    'value' => json_decode($value->value, true) ?: [],
                    'public_value' => json_decode($value->public_value, true) ?: [],
                    'is_updated_entry' => $value->is_updated_entry,
                    'rowHeader' => $value->row->slug,
                    'colHeader' => $value->column->slug,
                    'type' => $value->column->formType->name ?? 'Text'
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
     * @param array $previousMatrix Matrix returned from getChallengeMatrixData
     * @param array $newMatrix Incoming matrix from client payload
     * @return array Updated matrix with previous_value filled where applicable
     */
    public function setPreviousValueInMatrixData(array $previousMatrix, array $newMatrix): array
    {
        $previousByKey = [];
        foreach ($previousMatrix as $row) {
            foreach ($row as $cell) {
                $key = ($cell['rowHeader'] ?? '') . '|' . ($cell['colHeader'] ?? '');
                $previousByKey[$key] = [
                    'previous_value' => $cell['value'] ?? null,
                    /// 'is_updated_entry' => $cell['is_updated_entry'] ?? 0,
                ];
            }
        }

        foreach ($newMatrix as &$row) {
            foreach ($row as &$cell) {
                $key = ($cell['rowHeader'] ?? '') . '|' . ($cell['colHeader'] ?? '');
                if (isset($previousByKey[$key])) {
                    $previousValue = $previousByKey[$key]['previous_value'];
                    $currentValue = $cell['value'];
                    if ($previousValue && $currentValue && !empty(array_diff_assoc($previousValue, $currentValue))) {
                        $cell['previous_value'] = $previousByKey[$key]['previous_value'];
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
     * @param bool $isPlaceholder
     * @param int $categoryId
     * @param int $stepId
     * @param int|null $amountId
     * @param int $brokerId
     * @return Challenge|null
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
     * Handle placeholder challenge request
     */
    public function handlePlaceholderRequest(array $validatedData, int $brokerId, ?int $zoneId): array
    {
        $placeholderChallenge = $this->findChallengeByParams(
            true,
            $validatedData['category_id'],
            $validatedData['step_id'],
            null,
            $brokerId
        );

        if ($placeholderChallenge?->id) {
            return [
                'success' => true,
                'data' => [
                    'challenge_id' => $placeholderChallenge->id,
                    'matrix' => $this->getChallengeMatrixData($placeholderChallenge->id),
                    'evaluation_cost_discount' => $this->findDiscountByChallengeId($placeholderChallenge->id, $brokerId, $zoneId),
                    'affiliate_link' => $this->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, true, $zoneId),
                    'affiliate_master_link' => $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)
                ]
            ];
        }

        return [
            'success' => true,
            'message' => 'Placeholder challenge not found, return only the affiliate master link placeholder',
            'data' => [
                'affiliate_master_link' => $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)
            ]
        ];
    }

    /**
     * Handle regular challenge request
     */
    public function handleRegularChallengeRequest(array $validatedData, int $brokerId, ?int $zoneId): array
    {
        $challenge = $this->findChallengeByParams(
            false,
            $validatedData['category_id'],
            $validatedData['step_id'],
            $validatedData['amount_id'],
            $brokerId
        );

        if ($challenge?->id) {
            return $this->handleExistingChallenge($challenge, $brokerId, $zoneId, $validatedData);
        }

        return $this->handleMissingChallenge($brokerId, $zoneId, $validatedData);
    }

    /**
     * Handle existing challenge
     * If the challenge is found, return the challenge data for challenge matrix and matrix extradata:affiliate link, affiliate master link, evaluation cost discount
     * and add placeholder data if needed
     * @param Challenge $challenge
     * @param int $brokerId
     * @param int|null $zoneId
     * @param array $validatedData
     * @return array
     */
    public function handleExistingChallenge(Challenge $challenge, int $brokerId, ?int $zoneId, array $validatedData): array
    {
        $matrix = $this->getChallengeMatrixData($challenge->id);
        $discount = $this->findDiscountByChallengeId($challenge->id, $brokerId, $zoneId);
        $affiliateLink = $this->findUrlByUrlableTypeAndId(Challenge::class, $challenge->id, $brokerId, false, $zoneId);
        $affiliateMasterLink = $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, false, $zoneId);

        $responseArray = [
            'challenge_id' => $challenge->id,
            'matrix' => $matrix,
            'evaluation_cost_discount' => $discount,
            'affiliate_link' => $affiliateLink,
            'affiliate_master_link' => $affiliateMasterLink
        ];

        // Add placeholder data if needed
        $this->addPlaceholderDataIfNeeded($responseArray, $matrix, $discount, $affiliateLink, $affiliateMasterLink, $brokerId, $zoneId, $validatedData);

        return [
            'success' => true,
            'data' => $responseArray
        ];
    }

    /**
     * Handle missing challenge
     * If the challenge is not found, return matrix pleaceholders array and placeholder data for evaluation cost discount,
     *  affiliate link and affiliate master link
     * @param int $brokerId
     * @param int|null $zoneId
     * @param array $validatedData
     * @return array
     */
    public function handleMissingChallenge(int $brokerId, ?int $zoneId, array $validatedData): array
    {
        $affiliateMasterLinkObject = $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, false, $zoneId);
        $affiliateMasterLinkPlaceholder = $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)?->url;

        $responseArray = [
            'affiliate_master_link' => $affiliateMasterLinkObject
        ];

        if (is_null($affiliateMasterLinkObject)) {
            $responseArray['affiliate_master_link_placeholder'] = $affiliateMasterLinkPlaceholder;
        }

        // Add placeholder challenge data if available
        $placeholderChallenge = $this->findChallengeByParams(
            true,
            $validatedData['category_id'],
            $validatedData['step_id'],
            null,
            $brokerId
        );

        if ($placeholderChallenge?->id) {
            $responseArray = array_merge($responseArray, [
                'challenge_id' => $placeholderChallenge->id,
                'matrix' => null,
                'matrix_placeholders_array' => $this->getMatrixPlaceholderArray($placeholderChallenge->id, null),
                'evaluation_cost_discount_placeholder' => $this->findDiscountByChallengeId($placeholderChallenge->id, $brokerId, $zoneId)?->value,
                'affiliate_link_placeholder' => $this->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, true, $zoneId)?->url,
                'affiliate_master_link_placeholder' => $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)?->url,
            ]);
        }

        return [
            'success' => true,
            'data' => $responseArray
        ];
    }

    /**
     * Add placeholder data if needed
     * @param array &$responseArray
     * @param array $matrix
     * @param CostDiscountResource|null $discount
     * @param AffiliateLinkResource|null $affiliateLink
     * @param AffiliateLinkResource|null $affiliateMasterLink
     * @param int $brokerId
     * @param int|null $zoneId
     * @param array $validatedData
     * @return void
     */
    private function addPlaceholderDataIfNeeded(array &$responseArray, array $matrix, CostDiscountResource|null $discount, AffiliateLinkResource|null $affiliateLink, AffiliateLinkResource|null $affiliateMasterLink, int $brokerId, ?int $zoneId, array $validatedData): void
    {
        $placeholderChallenge = $this->findChallengeByParams(
            true,
            $validatedData['category_id'],
            $validatedData['step_id'],
            null,
            $brokerId
        );

        if (!$placeholderChallenge?->id) {
            return;
        }

        // Add matrix placeholders if matrix has empty cells
        if ($this->hasEmptyMatrixCells($matrix)) {
            $responseArray['matrix_placeholders_array'] = $this->getMatrixPlaceholderArray($placeholderChallenge->id, $responseArray['challenge_id']);
        }

        // Add placeholder data for null values
        if (is_null($discount)) {
            $responseArray['evaluation_cost_discount_placeholder'] = $this->findDiscountByChallengeId($placeholderChallenge->id, $brokerId, $zoneId)?->value;
        }

        if (is_null($affiliateLink)) {
            $responseArray['affiliate_link_placeholder'] = $this->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, true, $zoneId)?->url;
        }

        if (is_null($affiliateMasterLink)) {
            $responseArray['affiliate_master_link_placeholder'] = $this->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)?->url;
        }
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
     * @param int $placeholderChallengeId
     * @param int|null $challengeId
     * @return array
     */
    public function getMatrixPlaceholderArray(int $placeholderChallengeId, ?int $challengeId = null): array
    {
        $placeholderMatrix = $this->getChallengeMatrixData($placeholderChallengeId);
        $challengeMatrix = ($challengeId) ? $this->getChallengeMatrixData($challengeId) : null;
        return $this->extractPlaceholderValuesFromMatrix($placeholderMatrix, $challengeMatrix);
    }

    /**
     * Get placeholders cells array
     * Compares the placeholder matrix with the challenge matrix and returns the placeholders cells array for each empty cell in the challenge matrix
     * @param array $placeholderMatrix
     * @param array|null $matrix
     * @return array
     */
    private function extractPlaceholderValuesFromMatrix(array $placeholderMatrix, ?array $matrix = null): array
    {
        $placeholders = [];
        if ($matrix) {
            foreach ($matrix as $rowIndex => $row) {
                foreach ($row as $colIndex => $cell) {
                    if (empty($cell['value']['text']) || (is_array($cell['value']['text']) && empty(array_filter($cell['value']['text'])))) {
                        $placeholders[$cell['rowHeader'] . '-' . $cell['colHeader']] = $placeholderMatrix[$rowIndex][$colIndex]['value']['text'];
                    }
                }
            }
        } else {
            foreach ($placeholderMatrix as $rowIndex => $row) {
                foreach ($row as $colIndex => $cell) {
                    $placeholders[$cell['rowHeader'] . '-' . $cell['colHeader']] = $cell['value']['text'];
                }
            }
        }
        return $placeholders;
    }
}
