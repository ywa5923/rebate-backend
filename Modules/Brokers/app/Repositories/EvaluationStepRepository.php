<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\EvaluationStep;
use Illuminate\Database\Eloquent\Collection;

class EvaluationStepRepository
{
    public function __construct(protected EvaluationStep $model) {}

    public function getEvaluationSteps(int $broker_id,$language,$zone=null): Collection
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
}