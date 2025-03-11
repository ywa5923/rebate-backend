<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\Broker;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Models\Setting;
use Modules\Brokers\Transformers\BrokerCollection;
use Modules\Brokers\Transformers\SettingCollection;
use Modules\Brokers\Transformers\SettingResource;

class FilterRepository 
{
  
public function getBrokerCurrencyList()
{
    $results=[];
    Broker::select("account_currencies")->chunk(100,function ($brokers) use (&$results){
        $currencies=[];
        foreach($brokers as $broker)
        {
            //$broker->account_currencies
            //explode(",",$broker->account_currencies)
           array_push( $currencies,...explode(",",preg_replace('/\s+/', '', strtoupper($broker->account_currencies))));
        }

        array_push($results,...array_unique($currencies));
       
    });

    return array_unique(array_filter($results));
}

public function getBrokerStaticFieldList($langaugeCondition,$fieldName)
{
    
    $results=[];
    //if 'id' is not added in select function,it will not load translations ???? probably a laravel bug
    $this->getBrokerQB($langaugeCondition)->select('id',$fieldName)->chunk(100,function ($brokers) use (&$results,$fieldName){
          $list=[];
     
        //  dd($brokers[3]->translations);
          $brokerCollection=new BrokerCollection($brokers);
          foreach( $brokerCollection->resolve() as $broker)
          {
           
            $items=explode(",",$broker[$fieldName]);
            foreach($items as $item){
               if(!array_key_exists(trim($item),$list) && trim($item)!=="")
               $list[trim($item)]=trim($item);
            }
            
          }
         
         
          $results= array_merge($results,$list);
     });

    
     //dd( $qb->toSql(), $qb->getBindings());
   $uniqueList= array_unique($results);
   asort($uniqueList);
   return $uniqueList;


}

public function getBrokerQB($langaugeCondition)
{
   
        return Broker::with(["translations"=>function ( Builder $query) use ($langaugeCondition){
            /** @var  Illuminate\Contracts\Database\Eloquent\Builder   $query */
             $query->where(...$langaugeCondition);
         }]);
    
}

public function getSettingsParam($key,$languageCondition)
{

 $setting= Setting::with(["translations"=>function ( Builder $query) use ($languageCondition){
    /** @var  Illuminate\Contracts\Database\Eloquent\Builder   $query */
     $query->where(...$languageCondition);
 }])->where("key",$key)->get();
 $collection=(new SettingCollection($setting))->resolve();

 return json_decode($collection[0]["value"],true);
}

}