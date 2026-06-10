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
     */
    public function cloneMatrixValues(int $challengeId, array $newChallengeIds, ?int $zoneId = null): bool
    {
        try {
            $matrixValues = $this->model->where('challenge_id', $challengeId)->where('zone_id', $zoneId)->get();

            foreach ($matrixValues as $matrixValue) {
                foreach ($newChallengeIds as $newChallengeId) {
                    $matrixValue->replicate()->fill([
                        'challenge_id' => $newChallengeId,
                    ])->save();
                }
            }

            return true;
        } catch (\Throwable $e) {

            return false;
        }
    }

    /**
     * Clone matrix values for a challenge using insert
     */
    public function cloneMatrixValues2(int $challengeId, array $newChallengeIds, int $brokerId, ?int $zoneId = null): bool
    {
        $matrixValues = $this->model->where('challenge_id', $challengeId)->where('broker_id', $brokerId)->where('zone_id', $zoneId)->get();

        if ($matrixValues->isEmpty()) {
            return false;
        }

        //delete old matrix values
        $this->model->whereIn('challenge_id', $newChallengeIds)->where('broker_id', $brokerId)->where('zone_id', $zoneId)->delete();

        $insertData = [];
        $now = now();

        foreach ($matrixValues as $matrixValue) {
            // Exclude the ID and convert model attributes to an array
            $attributes = $matrixValue->toArray();
            unset($attributes['id']);

            // 1. Manually json_encode the JSON fields so the raw DB insert accepts them:
            foreach (['value', 'previous_value', 'public_value', 'previous_public_value'] as $jsonField) {
                if (isset($attributes[$jsonField]) && (is_array($attributes[$jsonField]) || is_object($attributes[$jsonField]))) {
                    $attributes[$jsonField] = json_encode($attributes[$jsonField]);
                }
            }

            // 2. Build the insert payload
            foreach ($newChallengeIds as $newChallengeId) {
                $insertData[] = array_merge($attributes, [
                    'challenge_id' => $newChallengeId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return $this->model->newQuery()->insert($insertData);
    }

    public function clone(int $challengeId, array $newChallengeIds, int $brokerId, ?int $zoneId = null)
    {
        $matrixRowsToClone = $this->model->where('challenge_id', $challengeId)->where('broker_id', $brokerId)->where('zone_id', $zoneId)->get();

        if ($matrixRowsToClone->isEmpty()) {
            return false;
        }

        $insertData = [];
        $now = now();
        foreach ($newChallengeIds as $newChallengeId) {

            $challengeMatrixRows = $this->model->newQuery()->where('challenge_id', $newChallengeId)->where('broker_id', $brokerId)->where('zone_id', $zoneId)->get();
            //if there are matrix rows for this challenge, copy public values from exitsing matrix rows and create a new batch of insert data
            if ($challengeMatrixRows->isNotEmpty()) {
                //copy public values from exitsing matrix rows and create a new batch of insert data
                foreach ($matrixRowsToClone as $matrixRowToClone) {

                    $challengeMatrixRow = $challengeMatrixRows->where('row_id', $matrixRowToClone->row_id)->where('column_id', $matrixRowToClone->column_id)->first();
                    if ($challengeMatrixRow) {
                        $insertData[] = array_merge($matrixRowToClone->toArray(), [
                            'challenge_id' => $newChallengeId,
                            'value' => json_encode($challengeMatrixRow->value),
                            'previous_value' => json_encode($challengeMatrixRow->value),
                            'public_value' => json_encode($challengeMatrixRow->public_value),
                            'previous_public_value' => json_encode($challengeMatrixRow->previous_public_value),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
                //now delete the old matrix rows
                //first delete translations

                $this->translationRepository->deleteByTranslationableTypeAndIds(ChallengeMatrixValue::class, $challengeMatrixRows->pluck('id')->toArray());
                //TO DO: add some observer to make again the translations for the new matrix rows

                //now delete the matrix rows
                $this->model->whereIn('challenge_id', $newChallengeIds)->where('broker_id', $brokerId)->where('zone_id', $zoneId)->delete();
            } else {
                //create a new batch of insert data
                foreach ($matrixRowsToClone as $matrixRowToClone) {
                    $insertData[] = array_merge($matrixRowToClone->toArray(), [
                        'challenge_id' => $newChallengeId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        return $this->model->newQuery()->insert($insertData);
    }

    public function clone3(int $challengeId, array $newChallengeIds, int $brokerId, bool $isAdmin, ?int $zoneId = null)
    {
        $matrixRowsToClone = $this->model->where('challenge_id', $challengeId)->where('broker_id', $brokerId)->where('zone_id', $zoneId)->get();

        if ($matrixRowsToClone->isEmpty()) {
            return false;
        }

        $insertData = [];
        $now = now();
        foreach ($newChallengeIds as $newChallengeId) {
            //if there are matrix rows for this challenge, update the rows with values from $matrixRowsToClone

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

        return $this->model->newQuery()->insert($insertData);
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
