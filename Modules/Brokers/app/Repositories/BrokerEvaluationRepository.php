<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\BrokerEvaluation;
use Illuminate\Database\Eloquent\Collection;

class BrokerEvaluationRepository
{
    public function __construct(protected BrokerEvaluation $model) {}

    //getByBrokerIdAndRuleIds
    public function getByBrokerIdAndRuleIds(int $brokerId, array $ruleIds): Collection
    {
        return BrokerEvaluation::query()
            ->where('broker_id', $brokerId)
            ->whereNull('zone_id')
            ->whereIn('evaluation_rule_id', $ruleIds)
            ->get()
            ->keyBy('evaluation_rule_id'); // optional: handy for lookups
    }

    /**
     * Get evaluations for a broker, optionally scoped by zone, with relations.
     */
    public function getByBrokerIdAndZone(int $brokerId, string $lang="en", ?int $zoneId = null): Collection
    {
        $qb = $this->model->newQuery()
            ->with(['evaluationRule', 'evaluationOption'])
            ->where('broker_id', $brokerId);

        if ($zoneId !== null) {
            $qb->where('zone_id', $zoneId);
        } else {
            $qb->whereNull('zone_id');
        }

        if($lang != "en"){
            $qb->with([
                'translations' => fn($q) => $q->where('language_code', $lang),
                'evaluationRule.translations' => fn($q) => $q->where('language_code', $lang),
                'evaluationOption.translations' => fn($q) => $q->where('language_code', $lang)]);
        }
        return $qb->get();
    }

    /**
     * Delete a broker evaluation by broker and id.
     */
    public function deleteByBrokerAndId(int $brokerId, int $id): int
    {
        return $this->model->newQuery()
            ->where('broker_id', $brokerId)
            ->whereKey($id)
            ->delete();
    }
}
