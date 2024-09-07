<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Models\Regulator;
use Modules\Brokers\Transformers\RegualtorCollection;

class RegulatorRepository
{


    public function getUniqueList(array $langaugeCondition)
    {
        $results=[];
        $this->getRegulatorsWithTranslationsQB($langaugeCondition)->chunk(100,function ($regulators) use (&$results){
          $list=[];
         $regulatorCollection=new RegualtorCollection($regulators);
          foreach($regulatorCollection->resolve() as $regulator)
          {
            //array_push($list, [$regulator["abreviation"]=>$regulator["abreviation"]."-".$regulator["country"]]);
            $list[trim($regulator["abreviation"])]=$regulator["abreviation"]."-".$regulator["country"];
          }

          $results=array_merge($results,$list);
        });
         
        return $results;
    }
    public function getRegulatorsWithTranslationsQB($langaugeCondition)
    {
        return Regulator::with(["translations"=>function (Builder $query)use ($langaugeCondition){
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
             $query->where(...$langaugeCondition);
         }]);
    }
}