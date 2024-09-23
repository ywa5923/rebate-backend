<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\OptionValue;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Transformers\DynamicOptionValueCollection;

class OptionValueRepository 
{

    public function getUniqueList($language,$slug)
    {
        $results=[];
       
         OptionValue::with(["translations"=>function (Builder $query) use ($language){
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
             $query->where(...$language);
         }])->where("option_slug","=",$slug)->chunk(100,function ($options) use (&$results){
            $collection=new DynamicOptionValueCollection($options);
             $list=[];
            foreach($collection->resolve() as $option)
            {
                $items=explode(",",$option["value"]);
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