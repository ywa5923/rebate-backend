<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\ChallengeRepository;
use Modules\Brokers\Models\Challenge;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ChallengeService
{
    protected ChallengeRepository $challengeRepository;

    public function __construct(ChallengeRepository $challengeRepository)
    {
        $this->challengeRepository = $challengeRepository;
    }

    /**
     * Store challenge with matrix data
     */
    public function storeChallengeMatrix(array $validatedData, int $brokerId,?int $zoneId=null,?bool $isAdmin=null): array
    {
        DB::beginTransaction();

        try {
            //check if challenge already exists
            //if it exist delete it
            $challenge = $this->challengeRepository->exists($validatedData['is_placeholder'],$validatedData['category_id'], $validatedData['step_id'], $validatedData['amount_id'] ?? null, $brokerId);
            if(!$challenge){
                //$challenge->delete();
                $challenge = $this->challengeRepository->create([
            
                    'is_placeholder' => $validatedData['is_placeholder'],
                    'challenge_category_id' => $validatedData['category_id'],
                    'challenge_step_id' => $validatedData['step_id'],
                    'challenge_amount_id' => $validatedData['amount_id'] ?? null,
                    'broker_id' => $brokerId
                ]);
                // Save matrix data
              $this->saveMatrixData($validatedData['matrix'], $challenge->id, $brokerId, $zoneId, $isAdmin);
            }else{
                $previousChalengeMatrix = $this->getChallengeMatrixData($challenge->id, $zoneId);
                $newMAtrix=$this->setPreviousValueInMatrixData(
                    $previousChalengeMatrix,
                     $validatedData['matrix'],
                );
                //remove old chalenge's matrix values
                $this->challengeRepository->deleteChallengeMatrixValues($challenge->id, $zoneId);// Save matrix data
                $this->saveMatrixData($newMAtrix, $challenge->id, $brokerId, $zoneId, $isAdmin);            }
            // Create challenge
           

            

            DB::commit();

            return [
                'success' => true,
                'challenge_id' => $challenge->id
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Save matrix data to challenge_matrix_values table
     */
    private function saveMatrixData(array $matrixData, int $challengeId, int $brokerId,?int $zoneId=null,?bool $isAdmin=null): void
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
                    'previous_value' => !empty($cellData['previous_value'])?json_encode($cellData['previous_value']):null,
                    'value' => !empty($cellData['value'])?json_encode($cellData['value']):null,
                    'public_value' => !empty($cellData['public_value'])?json_encode($cellData['public_value']):null,
                    'is_updated_entry' => $isAdmin ? 0 :$cellData['is_updated_entry'] ?? false,
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
    public function getChallengeMatrixData(int $challengeId,?int $zoneId=null): array
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
                    if($previousValue && $currentValue && !empty(array_diff_assoc($previousValue, $currentValue))){
                        $cell['previous_value'] = $previousByKey[$key]['previous_value'];
                        $cell['is_updated_entry']=true;
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
    ): ?Challenge
    {
        return $this->challengeRepository->exists($isPlaceholder, $categoryId, $stepId, $amountId, $brokerId);
    }
}