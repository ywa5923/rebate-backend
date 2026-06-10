<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\ChallengeMatrixValue;
use Modules\Translations\Repositories\TranslationRepository;

class ChallengeMatrixRepository
{
    public function __construct(
        protected TranslationRepository $translationRepository,
        protected ChallengeMatrixValue $model
    ) {
    }

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
                        if ($isAdmin) {
                            $challengeMatrixRow->update([
                                'public_value' => $matrixRowToClone->public_value,
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
     * chaining the value being replaced in front of the existing history:
     * {"text": "latestValue->prevValue->olderValue"}.
     *
     * @param  string|null  $currentValueJson  Raw JSON of the value being replaced, e.g. {"text": "7"}
     * @param  string|null  $previousValueJson  Raw JSON of the existing history, e.g. {"text": "5->3"}
     */
    public function buildPreviousValueHistory(?string $currentValueJson, ?string $previousValueJson): ?string
    {
        $historyParts = array_filter(
            [$this->extractTextFromJson($currentValueJson), $this->extractTextFromJson($previousValueJson)],
            fn (?string $text): bool => $text !== null && $text !== ''
        );

        if ($historyParts === []) {
            return null;
        }

        return json_encode(['text' => implode('->', $historyParts)]);
    }

    /**
     * Extract the "text" key from a raw JSON string like {"text": "7"}.
     */
    private function extractTextFromJson(?string $json): ?string
    {
        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return null;
        }

        $text = $decoded['text'] ?? null;

        return $text === null ? null : (string) $text;
    }
}
