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
     * Store challenge with matrix data
     */
    public function storeChallengeMatrix(array $validatedData, int $brokerId, ?int $zoneId = null, ?bool $isAdmin = null): array
    {
        DB::beginTransaction();

        try {
            //check if challenge already exists
            //if it exist delete it
            $challenge = $this->challengeRepository->exists((int)$validatedData['is_placeholder'], $validatedData['category_id'], $validatedData['step_id'], $validatedData['amount_id'] ?? null, $brokerId);
            $isPlaceholder = $validatedData['is_placeholder'];
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

                //save the affiliate link
                // $field = $isAdmin ? 'public_url' : 'url';
                // $validatedData['affiliate_link'] && $this->urlRepository->create([
                //     'urlable_type' => Challenge::class,
                //     'urlable_id' => $challenge->id,
                //     'url_type' => 'challenge-matrix',
                //     $field => $validatedData['affiliate_link'],
                //     'name' => 'Affiliate Link',
                //     'slug' => 'affiliate-link',
                //     'broker_id' => $brokerId,
                //     'is_placeholder' => $isPlaceholder,
                //     'zone_id' => $zoneId,
                // ]);

                $validatedData['affiliate_link'] && $this->urlRepository->saveAffiliateLink(
                    $challenge->id,
                    $validatedData['affiliate_link'],
                    'Affiliate Link',
                    $brokerId,
                    $isAdmin,
                    $isPlaceholder,
                    $zoneId,
                );

                $validatedData['affiliate_master_link'] && $this->urlRepository->saveAffiliateLink(
                    null,
                    $validatedData['affiliate_master_link'],
                    'Affiliate Master Link',
                    $brokerId,
                    $isAdmin,
                    $isPlaceholder,
                    $zoneId,
                );
                 //save the evaluation cost discount
                 if (!empty($validatedData['evaluation_cost_discount'])) {
                     $this->costDiscountRepository->createCostDiscount(
                        $challenge->id,
                        $validatedData['evaluation_cost_discount'],
                        $brokerId,
                        $isAdmin,
                        $zoneId,
                    );
                 }
               

            } else {

                //matrice saved by the admin or placeholder matrix will not have previous values
                //how matrix save works in frontend
                //1. if the admin is true and the matrix is changed, the cell's values will be injected in matrix's cell's public_values
                //2. if the admin is false and the matrix is changed, the cell's values will be injected in matrix's cell's values
                //3. In the placeholder mode ,is_placeholder is true, when the matrix is changed, the cell's values will be injected in matrix's cell's values


                //here we skip the previous value injection for admin and placeholder mode
                $newMatrix = ($isPlaceholder || $isAdmin) ? $validatedData['matrix'] : $this->setPreviousValueInMatrixData(
                    $this->getChallengeMatrixData($challenge->id, $zoneId),
                    $validatedData['matrix'],
                );
                //remove old chalenge's matrix values
                $this->challengeRepository->deleteChallengeMatrixValues($challenge->id, $zoneId); // Save matrix data
                $this->saveMatrixData($newMatrix, $challenge->id, $brokerId, $zoneId, $isAdmin);
                // $this->urlRepository->deleteByUrlableType(Challenge::class, $brokerId);


                //this is new 
                $oldCostDiscount = $this->costDiscountRepository->findByChallengeId($challenge->id, $brokerId, $zoneId);
                $oldAffiliateLink = $this->urlRepository->findByUrlableTypeAndId(Challenge::class, $challenge->id, $brokerId, $isPlaceholder, $zoneId);
                $oldAffiliateMasterLink = $this->urlRepository->findByUrlableTypeAndId(
                    Challenge::class, null, $brokerId, $isPlaceholder, $zoneId);
                
           
                //update the evaluation cost discount
                if ($oldCostDiscount) {
                    //if old evaluation cost discount is found, update it
                    $oldDiscountValue = $isAdmin ? $oldCostDiscount->public_value : $oldCostDiscount->broker_value;
                    
                    $newDiscountValue = $validatedData['evaluation_cost_discount']??null;
                    if ( $oldDiscountValue != $newDiscountValue && !is_null($newDiscountValue)) {
                        $oldCostDiscount->update([
                            ($isAdmin || $isPlaceholder) ? null : 'old_value' => $oldDiscountValue,
                            $isAdmin && !$isPlaceholder ? 'public_value' : 'broker_value' => $newDiscountValue,
                            'is_updated_entry' => $isAdmin ? false : true,
                        ]);
                    }elseif(is_null($newDiscountValue)){
                        $oldCostDiscount->delete();
                    }
                } else {
                    //if the old evaluation cost discount is not found, create a new one
                    if (!empty($validatedData['evaluation_cost_discount'])) {
                        $this->costDiscountRepository->createCostDiscount(
                            $challenge->id,
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
                            ($isAdmin || $isPlaceholder) ? null : 'old_url' => $oldAffiliateLink?->url,
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
                            $challenge->id,
                            $validatedData['affiliate_link'],
                            'Affiliate Link',
                            $brokerId,
                            $isAdmin,
                            $isPlaceholder,
                            $zoneId,
                        );
                    }
                }

                if ($oldAffiliateMasterLink) {
                    $oldAffiliateMasterLinkValue = $isAdmin ? $oldAffiliateMasterLink->public_url : $oldAffiliateMasterLink->url;
                    $newAffiliateMasterLinkValue = $validatedData['affiliate_master_link']??null;
                   
                    if ( $oldAffiliateMasterLinkValue != $newAffiliateMasterLinkValue && !is_null($newAffiliateMasterLinkValue)) {
                        $oldAffiliateMasterLink->update([
                            ($isAdmin || $isPlaceholder) ? null : 'old_url' => $oldAffiliateMasterLink->url,
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


    // public function saveAffiliateLink(
    //     ?int $challengeId=null, 
    //     string $affiliateLink, 
    //     string $affiliateLinkName, 
    //     int $brokerId, 
    //     ?bool $isAdmin = null,
    //     ?bool $isPlaceholder = false,
    //     ?int $zoneId = null,
    //    ): void
    // {
    //     $field = $isAdmin ? 'public_url' : 'url';


    //     $this->urlRepository->create([
    //         'urlable_type' => Challenge::class,
    //         'urlable_id' => $challengeId ?? null,
    //         'url_type' => 'challenge-matrix',
    //         $field => $affiliateLink,
    //         'name' => $affiliateLinkName,
    //         'slug' => strtolower(str_replace(' ', '-', $affiliateLinkName)),
    //         'broker_id' => $brokerId,
    //         'is_placeholder' => $isPlaceholder,
    //         'zone_id' => $zoneId,
    //     ]);
    // }
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
                    'is_updated_entry' => $isAdmin ? 0 : (int)($cellData['is_updated_entry'] ?? 0),
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
}
