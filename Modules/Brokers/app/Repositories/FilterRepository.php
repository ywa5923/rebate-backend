<?php

namespace Modules\Brokers\Repositories;
use Illuminate\Support\Facades\Log;
use Modules\Brokers\Models\Broker;
use JsonException;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Models\Setting;
use Modules\Brokers\Transformers\BrokerCollection;
use Modules\Brokers\Transformers\SettingCollection;
use Modules\Brokers\Transformers\SettingResource;

class FilterRepository
{

  public function getBrokerCurrencyList()
  {
    $results = [];
    Broker::select("account_currencies")->chunk(100, function ($brokers) use (&$results) {
      $currencies = [];
      foreach ($brokers as $broker) {
        //$broker->account_currencies
        //explode(",",$broker->account_currencies)
        array_push($currencies, ...explode(",", preg_replace('/\s+/', '', strtoupper($broker->account_currencies))));
      }

      array_push($results, ...array_unique($currencies));
    });

    return array_unique(array_filter($results));
  }

  public function getBrokerStaticFieldList($langaugeCondition, $fieldName)
  {

    $results = [];
    //if 'id' is not added in select function,it will not load translations ???? probably a laravel bug
    $this->getBrokerQB($langaugeCondition)->select('id', $fieldName)->chunk(100, function ($brokers) use (&$results, $fieldName) {
      $list = [];

      //  dd($brokers[3]->translations);
      $brokerCollection = new BrokerCollection($brokers);
      foreach ($brokerCollection->resolve() as $broker) {

        $items = explode(",", $broker[$fieldName]);
        foreach ($items as $item) {
          if (!array_key_exists(trim($item), $list) && trim($item) !== "")
            $list[trim($item)] = trim($item);
        }
      }


      $results = array_merge($results, $list);
    });


    //dd( $qb->toSql(), $qb->getBindings());
    $uniqueList = array_unique($results);
    asort($uniqueList);
    return $uniqueList;
  }

  public function getBrokerQB($langaugeCondition)
  {

    return Broker::with(["translations" => function (Builder $query) use ($langaugeCondition) {
      /** @var  Illuminate\Contracts\Database\Eloquent\Builder   $query */
      $query->where(...$langaugeCondition);
    }]);
  }


  /**
   * Returns the setting value for a given key, decoded from json.
   *
   * @param string $key
   * @param array $languageCondition
   * @return mixed
   * @throws \Exception
   */

  public function getSettingsParam($key, $languageCondition)
  {
    try{
      $setting = Setting::with(["translations" => function (Builder $query) use ($languageCondition) {
        /** @var  Illuminate\Contracts\Database\Eloquent\Builder   $query */
        $query->where(...$languageCondition);
      }])->where("key", $key)->get();

     
      if (!$setting) {
        return null; // Returnează null dacă setarea nu există
      }
    $resolvedCollection = (new SettingCollection($setting))->resolve();
    $jsonString = optional($resolvedCollection[0])['value'] ?? null;
    return json_decode(  $jsonString, $associative = true, $depth = 512, JSON_THROW_ON_ERROR);
   
    }catch(JsonException $e){
      Log::error("JSON decoding error: " . $e->getMessage());
      throw $e;

    }catch(\Exception $e){
      Log::error("Unexpected error: " . $e->getMessage());
        throw $e;
    }
    
   
   
    
  }
}