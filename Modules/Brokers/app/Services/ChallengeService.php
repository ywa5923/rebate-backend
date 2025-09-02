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
    public function storeChallenge(array $validatedData, int $brokerId): array
    {
        DB::beginTransaction();

        try {
            //check if challenge already exists
            //if it exist delete it
            $challenge = $this->challengeRepository->exists($validatedData['is_placeholder'],$validatedData['category_id'], $validatedData['step_id'], $validatedData['amount_id'] ?? null, $brokerId);
            if($challenge){
                $challenge->delete();
            }
            // Create challenge
            $challenge = $this->challengeRepository->create([
            
                'is_placeholder' => $validatedData['is_placeholder'],
                'challenge_category_id' => $validatedData['category_id'],
                'challenge_step_id' => $validatedData['step_id'],
                'challenge_amount_id' => $validatedData['amount_id'] ?? null,
                'broker_id' => $brokerId
            ]);

            // Save matrix data
            $this->saveMatrixData($validatedData['matrix'], $challenge->id, $brokerId);

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
    private function saveMatrixData(array $matrixData, int $challengeId, int $brokerId): void
    {
        $challengeMatrixValues = [];
        $groupNames = ['challenge', 'step-0', 'step-1', 'step-2'];

        foreach ($matrixData as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $cellData) {
                $rowHeaderSlug = $cellData['rowHeader'];
                $colHeaderSlug = $cellData['colHeader'];

                // Get matrix headers
                $rowHeader = $this->challengeRepository->getMatrixHeaderBySlugAndGroups($rowHeaderSlug, $groupNames);
                $colHeader = $this->challengeRepository->getMatrixHeaderBySlugAndGroups($colHeaderSlug, $groupNames);

                if (!$rowHeader || !$colHeader) {
                    throw new \Exception('Row or column header not found for: ' . $rowHeaderSlug . ' or ' . $colHeaderSlug);
                }

                $challengeMatrixValues[] = [
                    'value' => json_encode($cellData['value'] ?? []),
                    'public_value' => json_encode($cellData['public_value'] ?? []),
                    'is_invariant' => true,
                    'zone_id' => null,
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
    public function getChallengeMatrixData(int $challengeId): array
    {
        $matrixValues = $this->challengeRepository->getChallengeMatrixValues($challengeId);
        
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
                    'value' => json_decode($value->value, true) ?: [],
                    'public_value' => json_decode($value->public_value, true) ?: [],
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