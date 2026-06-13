<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\ChallengeMatrixValue;
use Modules\Translations\Repositories\TranslationRepository;
use Modules\Brokers\DTOs\ChallengeMatrixCellDTO;
use App\Exceptions\ApiException;

class ChallengeMatrixRepository
{
    public function __construct(
        protected TranslationRepository $translationRepository,
        protected ChallengeMatrixValue $model
    ) {}

    /**
     * Get challenge matrix values by challenge ID
     */
    public function getChallengeMatrixValues(int $challengeId, ?int $zoneId = null): Collection
    {
        return $this->model->where('challenge_id', $challengeId)->where('zone_id', $zoneId)->get();
    }

    /**
     * Clone matrix values for a challenge
     *
     * @return bool
     */
    public function clone(int $challengeId, array $newChallengeIds, int $brokerId, bool $isAdmin, ?int $zoneId = null)
    {
        $matrixRowsToClone = $this->model->where('challenge_id', $challengeId)->where('broker_id', $brokerId)->where('zone_id', $zoneId)->get();

        if ($matrixRowsToClone->isEmpty()) {
            return false;
        }

        $insertData = [];
        $now = now();
        foreach ($newChallengeIds as $newChallengeId) {
            //if there are matrix rows for this challenge, update the rows with values from $matrixRowsToClone

            //these are exisitng matrix rows for the new challenge
            //we need only to uodate them
            $challengeMatrixRows = $this->model->newQuery()->where('challenge_id', $newChallengeId)->where('broker_id', $brokerId)->where('zone_id', $zoneId)->get();

            if ($challengeMatrixRows->isNotEmpty()) {
                //update the existing matrix rows
                foreach ($challengeMatrixRows as $challengeMatrixRow) {
                    $matrixRowToClone = $matrixRowsToClone->where('row_id', $challengeMatrixRow->row_id)->where('column_id', $challengeMatrixRow->column_id)->first();
                    if ($matrixRowToClone) {
                        $isUpdatedEntry = $challengeMatrixRow->value == $matrixRowToClone->value ? 0 : 1;
                        $isUpdatedPublicEntry = $challengeMatrixRow->public_value == $matrixRowToClone->public_value ? 0 : 1;
                        if ($isAdmin) {
                            $challengeMatrixRow->update([
                                'public_value' => $matrixRowToClone->public_value,
                                'previous_public_value' => $isUpdatedPublicEntry
                                    ? $this->buildPreviousValueHistory($challengeMatrixRow->public_value, $challengeMatrixRow->previous_public_value)
                                    : $challengeMatrixRow->previous_public_value,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        } else {
                            $challengeMatrixRow->update([
                                'value' => $matrixRowToClone->value,
                                'previous_value' => $isUpdatedEntry
                                    ? $this->buildPreviousValueHistory($challengeMatrixRow->value, $challengeMatrixRow->previous_value)
                                    : $challengeMatrixRow->previous_value,
                                'is_updated_entry' => $isUpdatedEntry,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }
                }
            } else {
                //create a new batch of insert data
                foreach ($matrixRowsToClone as $matrixRowToClone) {
                    $attributes = $matrixRowToClone->toArray();
                    unset($attributes['id']);

                    if ($isAdmin) {
                        //copy only public_value which exist in $attributes, other keys are overwritten
                        $insertData[] = array_merge($attributes, [
                            'challenge_id' => $newChallengeId,
                            'value' => null,
                            'previous_value' => null,
                            'is_updated_entry' => 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    } else {
                        //copy only value which exist in $attributes, other keys are overwritten
                        $insertData[] = array_merge($attributes, [
                            'challenge_id' => $newChallengeId,
                            'public_value' => null,
                            'previous_value' => null,
                            'is_updated_entry' => 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }

        if (! empty($insertData)) {
            return $this->model->newQuery()->insert($insertData);
        }

        return true;
    }

    

    
     /**
      * Build the previous value history as valid JSON for the json column,
      */
    public function buildPreviousValueHistory(?string $currentValue, ?string $previousValue): ?string
    {
        return $currentValue . '->' . $previousValue;
    }

  

    public function updateChallengeMatrixValue(bool $isPlaceholder, ChallengeMatrixCellDTO $cellDTO, int $challengeId, ?int $brokerId, ?bool $isAdmin = null, ?int $zoneId = null): void
    {

        //first get the existing cell value to compare and set the previous value and is_updated_entry
        $existingCell = $this->model
            ->where('challenge_id', $challengeId)
            ->where('id', $cellDTO->id)
            ->where('broker_id', $brokerId)
            ->when(
                $zoneId === null,
                fn($query) => $query->whereNull('zone_id'),
                fn($query) => $query->where('zone_id', $zoneId)
            )
            // ->whereHas('row', function($query) use ($cellDTO){
            //     $query->where('slug', $cellDTO->rowSlug);
            // })->whereHas('column', function($query) use ($cellDTO){
            //     $query->where('slug', $cellDTO->colSlug);
            // })
            ->first();
        if (!$existingCell) {
            throw new ApiException('Cell not found', 404);
        }
        //for placeholder mode, placeholder texts are stored in public_value
        if($isPlaceholder){
            $existingCell->update([
                'value' => $cellDTO->value,
                'is_updated_entry' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        } 
        if (!$isAdmin) {

            if ($existingCell->value != $cellDTO->value) {

                $existingCell->update([
                    'value' => $cellDTO->value,
                    'previous_value' => ($existingCell->value??'empty') . '->' . $existingCell->previous_value,
                    'is_updated_entry' => 1,
                ]);
            }
        } else {

            //for admin we need to update is_updated_entry to 0 even if the public value is not changed
            //this will clear the red flag in the frontend
            $existingCell->update([
                    'previous_public_value' => ($existingCell->public_value??'empty') . '->' . ($existingCell?->previous_public_value??''),
                    'public_value' => $cellDTO->publicValue,
                    'is_updated_entry' => 0,
                ]);
            
        }
    }
}
