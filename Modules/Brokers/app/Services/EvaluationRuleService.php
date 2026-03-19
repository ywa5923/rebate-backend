<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\EvaluationRuleRepository;
use Modules\Brokers\Repositories\BrokerEvaluationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Modules\Brokers\Models\BrokerEvaluation;
use Illuminate\Support\Collection;

class EvaluationRuleService
{
    public function __construct(
        protected EvaluationRuleRepository $evaluationRuleRepository,
        protected BrokerEvaluationRepository $brokerEvaluationRepository
    ) {}

    public function upsertEvaluationRule(array $data, $broker_id, $is_admin, ?int $zone_id = null)
    {
        //Incoming data format:
        // array:8 [ /
        //     "copy-trading#1" => "2"
        //     "scalping#2" => "4"
        //     "hedging#3" => "6"
        //     "buy-back#4" => "8"
        //     "copy-trading#1_getter" => null
        //     "scalping#2_getter" => "NFGGG"
        //     "hedging#3_getter" => null
        //     "buy-back#4_getter" => null
        //   ]
        //=============
        //1 Extract the evaluation rule id which is the number after the # in the key
        //2 The array value is the evaluation option id
        //3 If the key ends with _getter, the array value is the details and the evaluation_rule_id is the number after the # in the key
        //4 so we need to append the getter details to the entries array which contains the evaluation option id
       
        $entries = [];

        foreach ($data as $key => $value) {
            if (!preg_match('/#(\d+)(?:_getter)?$/', $key, $m)) {
                throw new \InvalidArgumentException("Invalid key: $key");
            }
            $ruleId = (int) $m[1];

            if (str_ends_with($key, '_getter')) {
                $entries[$ruleId]['details'] = $value ?: null;
            } else {
                $entries[$ruleId]['evaluation_option_id'] = (int) $value;
            }
        }
        //for broker, get the existing entries to set the previous evaluation option id and details
        if (!$is_admin) {
            $ruleIds = array_keys($entries);
            $existingEntries = $this->brokerEvaluationRepository->getByBrokerIdAndRuleIds($broker_id, $ruleIds);
        }


        $dbData = [];
        $detailsKey = $is_admin ? 'public_details' : 'details';
        $optionIdKey = $is_admin ? 'public_evaluation_option_id' : 'evaluation_option_id';
        foreach ($entries as $ruleId => $row) {
            if (!isset($row['evaluation_option_id'])) {
                throw new \InvalidArgumentException("Missing option for rule $ruleId");
            }
            $item = [
                'broker_id'           => $broker_id,
                'evaluation_rule_id'  => $ruleId,
                $optionIdKey => $row['evaluation_option_id'],
                $detailsKey             => $row['details'] ?? null,
                'is_updated_entry' => false,
                'updated_at' => now(),
            ];
            //for broker, set the previous evaluation option id and details
            if (!$is_admin) {
                $existingEntry = $existingEntries->get($ruleId);
                if ($existingEntry) {
                    if($existingEntry->evaluation_option_id != $row['evaluation_option_id']){
                        $item['previous_evaluation_option_id'] = $existingEntry->evaluation_option_id;
                        $item['is_updated_entry'] = true;
                    }
                    if($existingEntry->details != $row['details']){
                        $item['previous_details'] = $existingEntry->details;
                        $item['is_updated_entry'] = true;
                    }
                }
            }
            $item['zone_id'] = $zone_id;
            $dbData[] = $item;
        }

         //Note!!! Upsert doesnt works correctly with unique key when zone_id is null, so we use updateOrInsert instead
        // if (!$is_admin) {
        //     $upsertArray = ['evaluation_option_id', 'details', 'previous_evaluation_option_id', 'previous_details', 'updated_at'];
        // } else {
        //     $upsertArray = ['public_evaluation_option_id', 'public_details', 'updated_at'];
        // }

        // foreach ($dbData as &$r) {
        //     // add any missing required keys with null
        //     $r += array_fill_keys($upsertArray, null);
        // }
        // unset($r);
        // DB::table('broker_evaluations')->upsert(
        //     $dbData,
        //     ['broker_id', 'evaluation_rule_id','zone_id'],
        //     // update all (Laravel expects column names here)
        //     $upsertArray
        // );

        foreach ($dbData as $row) {
            DB::table('broker_evaluations')->updateOrInsert(
                [
                    'broker_id' => $row['broker_id'],
                    'evaluation_rule_id' => $row['evaluation_rule_id'],
                    'zone_id' => $row['zone_id'] ?? null, // NULL matches via whereNull
                ],
                Arr::except($row, ['broker_id','evaluation_rule_id','zone_id'])
            );
        }
    }


    /**
     * Get broker evaluations (scoped by optional zone).
     * Returns a collection for controller ->toArray().
     */
    public function getEvaluations(int $brokerId,string $lang="en", ?int $zoneId = null): Collection
    {
        return $this->brokerEvaluationRepository->getByBrokerIdAndZone($brokerId, $lang, $zoneId);
    }

    public function deleteEvaluation(int $brokerId, int $id): bool
    {
        return $this->brokerEvaluationRepository->deleteByBrokerAndId($brokerId, $id) > 0;
    }
}
