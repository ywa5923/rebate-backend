<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\EvaluationRule;
use Illuminate\Database\Eloquent\Collection;
class EvaluationRuleRepository
{
    public function __construct(protected EvaluationRule $model){}

    // public function getAll($lang="en",$zoneId=null): Collection
    // {
    //     $qb=$this->model->with(['evaluationOptions' => function($query) use ($lang) {
    //         $query->with(['translations' => function($query) use ($lang) {
    //             $query->where('language_code', $lang);
    //         }]);
    //     },'translations' => function($query) use ($lang) {
    //         $query->where('language_code', $lang);
    //         }]);
    //     if($zoneId){
    //         $qb->where('zone_id', $zoneId);
    //     }
    //     return $qb->get();
    // }

    public function getAll($zoneId=null): Collection
    {
        $qb = $this->model->with(['evaluationOptions'])->whereNull('zone_id');
        if ($zoneId) {
            $qb->orWhere('zone_id', $zoneId);
        }
        return $qb->get();
    }

    public function getAllByLanguageAndZone(string $lang,int $zoneId): Collection
    {
       return $this->model->with([
            'evaluationOptions.translations' => fn($q) => $q->where('language_code', $lang),
            'translations' => fn($q) => $q->where('language_code', $lang),
        ])->where('zone_id', $zoneId)->get();
       
    }

    
}