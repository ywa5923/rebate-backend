<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\OptionValue;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Transformers\DynamicOptionValueCollection;

class OptionValueRepository 
{

    public function getUniqueList($language,$slug,$zoneCondition)
    {
        $results=[];
        //dd($language,$slug,$zoneCondition);
         OptionValue::with(["translations"=>function (Builder $query) use ($language){
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
             $query->where(...$language);
             
         }])->where("option_slug","=",$slug)->where(function (Builder $query) use ($zoneCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
           
            $query->where(...$zoneCondition)->orWhere('is_invariant', true);
        })
         
        ->chunk(100,function ($options) use (&$results){
            $collection=new DynamicOptionValueCollection($options);
             $list=[];
            foreach($collection->resolve() as $option)
            {
                
                //if the optionValue contain a link we will keep only the text
                preg_match('/<a[^>]*>(.*?)<\/a>/', $option["value"], $match);
                $optionValue=($match)?$match[1]:$option["value"];

                //$items=explode(",",$option["value"]);
                $items=explode(",",$optionValue);
                foreach($items as $item){
                   if(!array_key_exists(trim($item),$list) && trim($item)!=="")
                   $list[trim($item)]=trim($item);
                }
            }
            $results= array_merge($results,$list);
         });

         return array_unique($results);
    }
}