<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\EvaluationStep;
use Illuminate\Database\Eloquent\Collection;

class EvaluationStepRepository
{
    public function __construct(protected EvaluationStep $model) {}

    /**
     * Get evaluation steps for a broker, optionally scoped by zone, with relations.
     * @param int $broker_id
     * @param string $language
     * @param int|null $zone
     * @return Collection
     */
    public function getEvaluationSteps(int $broker_id,string $language="en",?int $zone=null): Collection
    {
        //return $this->model->where('broker_id', $broker_id)->with('optionValues')->orderBy('created_at', 'desc')->get();

        $qb = $this->model->where('broker_id', $broker_id)->with(['optionValues'=>function($query) use ($zone) {
           
            if($zone){
                $query->orWhere('zone_id', $zone);
            }else{
                $query->whereNull('zone_id');
            }
        },'optionValues.translations'=> function($query) use ($language) {
            $query->where('language_code', $language);
        }]);
        
        return $qb->orderBy('created_at', 'desc')->get();
    }

    /**
     * Delete an evaluation step by id and broker id.
     * @param int $id
     * @param int $broker_id
     * @return EvaluationStep|null
     */
    public function deleteEvaluationStep(int $id, int $broker_id): ?EvaluationStep
    {
        $evaluationStep = $this->model->where('id', $id)->where('broker_id', $broker_id)->first();
        $deleted = $evaluationStep->delete();
        if (!$deleted) {
            return null;
        }
        return $evaluationStep;
    }   
}