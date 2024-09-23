<?php

namespace Modules\Brokers\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\Company;
use Modules\Brokers\Models\OptionValue;
use Modules\Brokers\Transformers\BrokerCollection;
use Modules\Translations\Models\Translation;

class BrokerRepository implements RepositoryInterface
{
    use BrokerTrait;

    public function getFullProfile($languageCondition)
    {
        //dd($languageCondition);
        $bc = new BrokerCollection(Broker::with(['translations' => function (Builder $query) use ($languageCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where("language_code", $languageCondition[2]);
        }, 'dynamicOptionsValues.translations' => function (Builder $query) use ($languageCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where("language_code", $languageCondition[2]);
        }])->paginate());

        return $bc;

        // $this->borkerJsonFilter($bc->toJson());


    }

    public function getDynamicColumns2($languageCondition, $dynamicColumns)
    {
        $bc = Broker::select(["id", "logo"])->with(['translations' => function (Builder $query) use ($languageCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where("language_code", $languageCondition[2]);
        }, 'dynamicOptionsValues' => function (Builder $query) use ($dynamicColumns) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->whereIn("option_slug", $dynamicColumns);
        }, 'dynamicOptionsValues.translations' => function (Builder $query) use ($languageCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where("language_code", $languageCondition[2]);
        }])->paginate();

        return $bc;
    }

    public function getDynamicColumns($languageCondition, $columns, $orderBy, $orderDirection, $filters)
    {

        //to be added in env.file or global params
        $tableStaticColumns = ['home_url', 'user_rating', 'account_type', 'trading_name', 'overall_rating', 'support_options', 'account_currencies', 'trading_instruments'];
        $tableExtColumns = ['regulators'];
        if (empty($columns))
            $columns = $tableStaticColumns;


        $dynamicColumns = array_diff($columns, array_merge($tableStaticColumns, $tableExtColumns));
        $selectedStaticColumns = array_intersect($tableStaticColumns, $columns);
        $selectedExtColumns = array_intersect($tableExtColumns, $columns);

        if (empty($selctedStaticColumns) && empty($dynamicColumns))
            $selctedStaticColumns = $tableStaticColumns;

        $jsonResult = $this->getQueryJson($languageCondition,  $selectedStaticColumns, $dynamicColumns, $selectedExtColumns, $orderBy, $orderDirection, $filters);

       

        return new BrokerCollection($jsonResult);

        //return $jsonResult;
    }
    public function getQueryJson($languageCondition, $staticColumns, $dynamicColumns, $extColumns, $orderBy, $orderDirection, $filters)
    {
        // $query=Company::query();
        // $brokersId=[];
        // if(isset($filters["offices"]) && !empty($filters["offices"]) && $languageCondition[2]=="en")
        // {
        //     $offices=$filters["offices"];
        //     $brokers = Broker::whereHas('companies', function ($query) use ($offices) {
        //         $index=0;
        //         foreach($offices as $office)
        //     {
        //         if($index==0){
        //             $query->where("offices",'LIKE',"%".$office."%");
        //         }else{
        //             $query->orWhere("offices",'LIKE',"%".$office."%");
        //         }

        //         $index++;
        //     }
        //     });



        // //dd(DB::getQueryLog());

        //    // $result=$query->pluck('id')->toArray();

        // }else{
        //     $offices=$filters["offices"];
        //     $brokers = Broker::whereHas('companies.translations', function ($query) use ($offices) {
        //         $index=0;
        //         $query->where(function($query){
        //             $query->where("translations.property",'=','offices');
        //         })->where(function($query) use ($offices){
        //            $index=0;
        //             foreach($offices as $office)
        //             {
        //                 if($index==0){
        //                     $query->where("translations.value",'LIKE',"%".$office."%");
        //                 }else{
        //                     $query->orWhere("translations.value",'LIKE',"%".$office."%");
        //                 }

        //                 $index++;
        //             }
        //         });


        //     });
        //     //Seychelles,Cyprus
        //    // dd($brokers->toSql(),$brokers->getBindings());
        //     $brokersId=$brokers->pluck('id')->toArray();
        // }

        //dd($brokersId);
        DB::enableQueryLog();
        $bc = Broker::select(["id", ...$staticColumns])->with([
            'translations' => function (Builder $query) use ($languageCondition) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where("language_code", $languageCondition[2]);
            },
            'dynamicOptionsValues' => function (Builder $query) use ($dynamicColumns) {

                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->whereIn("option_slug", $dynamicColumns);
            },
            'dynamicOptionsValues.translations' => function (Builder $query) use ($languageCondition) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where("language_code", $languageCondition[2]);
            }
        ]);
        if (!empty($extColumns)) {
            foreach ($extColumns as $relationName) {

                $bc = $bc->with([$relationName, $relationName . ".translations" => function (Builder $query) use ($languageCondition) {
                    /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                    $query->where("language_code", $languageCondition[2]);
                }]);
            }
        }

        if (isset($filters["whereIn"])) {
             $this->addWhereInFilters($bc, $filters["whereIn"], $languageCondition);
        }

        
        if (isset($filters["where"])) {
            $this->addWhereFilters($bc, $filters["where"], $languageCondition);
       }
        // "companies","companies.translations"=> function (Builder $query) use ($languageCondition) {
        //         /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
        //         $query->where("language_code", $languageCondition[2]);
        //     },"regulators","regulators.translations"=>function (Builder $query) use ($languageCondition) {
        //         /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
        //         $query->where("language_code", $languageCondition[2]);
        //     }

        if (!empty($orderBy) && !empty($orderDirection)) {
            // $bc=$bc->addSelect([
            //     $orderBy[0]=>Translation::select("translations.value")
            //     ->join("option_values",function ($join) use ($languageCondition,$orderBy){
            //        $join->on("translations.translationable_id","=","option_values.id")
            //        ->where("translations.translationable_type",'=',OptionValue::class)
            //        ->where("translations.language_code","=",$languageCondition[2])
            //        ->where("translations.property","=", $orderBy[0]);
            //    })->whereColumn("option_values.broker_id",'=',"brokers.id")->where("option_values.option_slug","=", $orderBy[0])

            //     ])
            //     ->orderBy( $orderBy[0],$orderDirection);

            $tableStaticColumns = ['home_url', 'user_rating', 'account_type', 'trading_name', 'overall_rating', 'support_options', 'account_currencies', 'trading_instruments'];
            $orderQueryType = "";
            $orderQueryType = in_array($orderBy, $tableStaticColumns) ? "static" : "dynamic";
            $translatedEntry = Translation::where(
                [$languageCondition, ['property', '=', $orderBy]]
            )->first();
            if (!is_null($translatedEntry)) {
                if ($translatedEntry->translationable_type == Broker::class) {
                    $orderQueryType = "translated-static";
                }

                if ($translatedEntry->translationable_type == OptionValue::class) {
                    $orderQueryType = "translated-dynamic";
                }
            }

            $bc = $this->addOrderBy($bc, $orderBy, $orderDirection, $languageCondition, $orderQueryType);
        }


        //  ->where("brokers.trading_name",'LIKE','%Trade%')
        //  ->orWhereHas('dynamicOptionsValues.translations',function(Builder $query){
        //     /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
        //     $query->where("translations.property","=","trading_fees")->where("translations.language_code","=",'ro')->where("translations.value","=","NU");
        //  })

        $bc = $bc->paginate();



        //  dd(DB::getQueryLog());
        // dd($bc->toSql());
        return $bc;
    }

    public function addWhereFilters($queryBuilder, $filters, $languageCondition) {
        if(isset($filters["filter_min_deposit"])){

            $this->whereDynamicOption($queryBuilder,$filters["filter_min_deposit"]);
        }
       
    }

    public function addWhereInFilters($queryBuilder, $filters, $languageCondition)
    {

       // dd($filters);
        //the filter array looks like
        // array:1 [ 
        //     "filter_offices" => array:2 [
        //       0 => "offices"
        //       1 => array:1 [
        //         0 => "Greece"
        //       ]
        //     ]
        //   ]

     // dd($filters);  
     if(isset($filters["filter_trading_instruments"]))
     {
        $this->addBrokerStaticFilter($queryBuilder,$filters["filter_trading_instruments"][0],$filters["filter_trading_instruments"][1]);
     }
     if(isset($filters["filter_support_options"])){
        $this->addBrokerStaticFilter($queryBuilder,$filters["filter_support_options"][0],$filters["filter_support_options"][1]);
     }
     if(isset($filters["filter_account_currency"]))
     {
        $this->addBrokerStaticFilter($queryBuilder,$filters["filter_account_currency"][0],$filters["filter_account_currency"][1]);
       
     }

     if(isset($filters["filter_offices"])){
        $this->addCompanyWhereInFilters($queryBuilder, $filters["filter_offices"], $languageCondition);
     }
     if(isset($filters["filter_headquarters"])){
        $this->addCompanyWhereInFilters($queryBuilder, $filters["filter_headquarters"], $languageCondition);
     }

     if(isset($filters["filter_withdrawal_methods"]))
     {
        $this->whereInDynamicOption($queryBuilder,$filters["filter_withdrawal_methods"],$languageCondition);
     }

     if(isset($filters["filter_group_trading_account_info"]))
     {
        $this->addDynamicBooleanFilters($queryBuilder,$filters["filter_group_trading_account_info"][1]);
     }

        // if (isset($filters['filter_offices'])) {
        //     if ($languageCondition[2] == "en") {
        //         return $queryBuilder->whereHas('companies', function ($query) use ($filters) {
        //             $index = 0;
        //             foreach ($filters['filter_offices'][1] as $office) {
        //                 if ($index == 0) {
        //                     $query->where("offices", 'LIKE', "%" . $office . "%");
        //                 } else {
        //                     $query->orWhere("offices", 'LIKE', "%" . $office . "%");
        //                 }

        //                 $index++;
        //             }
        //         });
        //     } else {

        //         return $queryBuilder->whereHas('companies.translations', function ($query) use ($filters, $languageCondition) {
        //             $index = 0;
        //             $query->where(function ($query) use ($languageCondition) {
        //                 $query->where("translations.property", '=', 'offices');
        //                 $query->where("translations.language_code", '=', $languageCondition[2]);
        //             })->where(function ($query) use ($filters) {
        //                 $index = 0;
        //                 foreach ($filters['filter_offices'][1]  as $office) {
        //                     if ($index == 0) {
        //                         $query->where("translations.value", 'LIKE', "%" . $office . "%");
        //                     } else {
        //                         $query->orWhere("translations.value", 'LIKE', "%" . $office . "%");
        //                     }

        //                     $index++;
        //                 }
        //             });
        //         });
        //     }
        // }
    }

    public function addBrokerStaticFilter($queryBuilder,$columnName,$filters)
    {
       
          foreach($filters as $filter){
         
            $queryBuilder->where("brokers.".$columnName,'LIKE','%'.$filter.'%');
            
          }

          //dd($queryBuilder->toSql(),$queryBuilder->getBindings());
    }
    public function addDynamicBooleanFilters($queryBuilder,$slugArray)
    {
      // dd($slugArray);
    
        $queryBuilder->whereHas('dynamicOptionsValues', function ($query) use ($slugArray) {
            $index=0;
            foreach($slugArray as $slug)
            {


                  if($index===0){
                    $query->where(function($query) use ($slug){
                        $query->where("option_slug",$slug)->where("value",true);
                    });
                }else{
                    $query->orWhere(function($query) use ($slug){
                        $query->where("option_slug",$slug)->where("value",true);
                    });
                }
                
                // $query->where(function($query) use ($slug){
                   
                //     $query->where("option_slug",$slug)->where("value",true);
                   
                // });
                $index++;
            }

               
              
                
             
        });

        //dd($queryBuilder->toSql(),$queryBuilder->getBindings());
    }
    public function addCompanyWhereInFilters($queryBuilder, $filters, $languageCondition)
    {
       
        //rhw filter array looks like
         // array:2 [
        //       0 => "offices"
        //       1 => array:1 [
        //         0 => "Greece"
        //       ]
        //     ]
        if ($languageCondition[2] == "en") {

           
                 $queryBuilder->whereHas('companies', function ($query) use ($filters) {
                    $index = 0;
                    foreach ($filters[1] as $filter) {
                        if ($index == 0) {
                            $query->where($filters[0], 'LIKE', "%" . $filter . "%");
                        } else {
                            $query->orWhere($filters[0], 'LIKE', "%" . $filter . "%");
                        }

                        $index++;
                    }
                });
          
        } else{
           
         $queryBuilder->whereHas('companies.translations', function ($query) use ($filters, $languageCondition) {
                   
                    $query->where(function ($query) use ($languageCondition,$filters) {
                        $query->where("translations.property", '=', $filters[0]);
                        $query->where("translations.language_code", '=', $languageCondition[2]);
                    })->where(function ($query) use ($filters) {
                        $index = 0;
                        foreach ($filters[1]  as $filter) {
                            
                            if ($index == 0) {
                                $query->where("translations.value", 'LIKE', "%" . $filter . "%");
                            } else {
                                $query->orWhere("translations.value", 'LIKE', "%" . $filter . "%");
                            }

                            $index++;
                        }
                    });
                });

        }//end else
       // dd($queryBuilder->toSql(),$queryBuilder->getBindings());
    }

    public function whereDynamicOption($queryBuilder,$whereCondition,$translate=null)
    {
        if(is_null($translate)){
            $queryBuilder->whereHas('dynamicOptionsValues', function ($query) use ($whereCondition) {
                $query->where("option_slug",$whereCondition[0])->where("value",$whereCondition[1],$whereCondition[2]);
            });
        }
        
  
    }

    public function whereInDynamicOption($queryBuilder,$whereInCondition,$languageCondition)
    {
        // Example of whereInCondition
        //   [
        //     0 => "withdrawal_methods"
        //     1 => array:2 [
        //       0 => "Bank of Valletta"
        //       1 => "Baltikums Bank"
        //     ]
        //   ]

        if($languageCondition[2]=='en'){
            $queryBuilder->whereHas('dynamicOptionsValues', function ($query) use ($whereInCondition) {
                $query->where("option_slug",$whereInCondition[0])->where(function ($query) use ($whereInCondition) {
                    $index = 0;
                    foreach ($whereInCondition[1]  as $filter) {
                        
                        if ($index == 0) {
                            $query->where("value", 'LIKE', "%" . $filter . "%");
                        } else {
                            $query->orWhere("value", 'LIKE', "%" . $filter . "%");
                        }

                        $index++;
                    }
                });;
            });
        }else{
            $queryBuilder->whereHas('dynamicOptionsValues.translations', function ($query) use ($whereInCondition,$languageCondition) {
                $query->where(function ($query) use ($languageCondition,$whereInCondition) {
                    $query->where("property", '=', $whereInCondition[0]);
                    $query->where(...$languageCondition);
                })->where(function ($query) use ($whereInCondition) {
                    $index = 0;
                    foreach ($whereInCondition[1]  as $filter) {
                        
                        if ($index == 0) {
                            $query->where("value", 'LIKE', "%" . $filter . "%");
                        } else {
                            $query->orWhere("value", 'LIKE', "%" . $filter . "%");
                        }

                        $index++;
                    }
                });
            });
        }

    }
    public function addOrderBy($queryBuilder, $orderBy, $orderDirection, $languageCondition, $queryType)
    {
        if ($queryType == "translated-dynamic") {
            return $queryBuilder->addSelect([
                $orderBy => Translation::select("translations.value")
                    ->join("option_values", function ($join) use ($languageCondition, $orderBy) {
                        $join->on("translations.translationable_id", "=", "option_values.id")
                            ->where("translations.translationable_type", '=', OptionValue::class)
                            ->where("translations.language_code", "=", $languageCondition[2])
                            ->where("translations.property", "=", $orderBy);
                    })->whereColumn("option_values.broker_id", '=', "brokers.id")->where("option_values.option_slug", "=", $orderBy)

            ])
                ->orderBy($orderBy, $orderDirection);
        }

        if ($queryType == "translated-static") {

            return $queryBuilder->addSelect([
                $orderBy => Translation::select("translations.value")
                    ->whereColumn("translations.translationable_id", "=", "brokers.id")
                    ->where("translations.language_code", "=", $languageCondition[2])
                    ->where("translations.property", "=", $orderBy)
                    ->where("translationable_type", "=", Broker::class)
            ])
                ->orderBy($orderBy, $orderDirection);
        }

        if ($queryType == 'static') {
            return $queryBuilder->orderBy($orderBy, $orderDirection);
        }

        if ($queryType == 'dynamic') {

            return $queryBuilder->addSelect([$orderBy => OptionValue::select("value")->whereColumn("option_values.broker_id", '=', "brokers.id")
                ->where("option_values.option_slug", "=", $orderBy)])
                ->orderBy($orderBy, $orderDirection);
        }
    }
    public function getDynamicColumns12($languageCondition, $dynamicColumns)
    {
        $bc = Broker::with(['translations' => function (Builder $query) use ($languageCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where("language_code", $languageCondition[2]);
        }, 'dynamicOptionsValues' => function (Builder $query) use ($dynamicColumns) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->whereIn("option_slug", $dynamicColumns);
        }, 'dynamicOptionsValues.translations' => function (Builder $query) use ($languageCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where("language_code", $languageCondition[2]);
        }])->get();



        //nu ordoneaza dupa o relatie a relatiei
        //  ->orderBy(OptionValue::select("value")->whereColumn("option_values.broker_id","brokers.id")
        //  ->where("option_values.option_slug","=",'position_home'),"desc")
        //  ->get();

        //  ->orderBy(Translation::select("value")->whereColumn("translations.translationable_id","brokers.id")
        //  ->where("translations.translationable_type",'=',"Modules\\Brokers\\Models\\Broker")
        //  ->where("translations.property","=",'account_type'),"asc")
        return $bc;
    }

    public function getDynamicColumns5($languageCondition, $dynamicColumns)
    {
        $bc = Broker::leftJoin('translations', function ($join) use ($languageCondition) {

            $join->on("translations.translationable_id", '=', 'brokers.id')
                ->where("translations.translationable_type", '=', 'Modules\Brokers\Models\Broker')
                ->where("translations.language_code", '=', $languageCondition[2]);
        })->with(['translations' => function (Builder $query) use ($languageCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            // $query->where("language_code",$languageCondition[2]);
        }])->paginate();

        return $bc;
    }
    public function getDynamicColumns34($languageCondition, $dynamicColumns)
    {
        $bc = Broker::leftJoin('translations', "translations.translationable_id", '=', 'brokers.id')
            ->orderBy("translations.property", "desc")->orderBy("translations.value", "desc")
            ->with(['translations' => function (Builder $query) use ($languageCondition) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where("language_code", $languageCondition[2]);
            }])->select('brokers.*')->get();
        return $bc;

        //https://www.youtube.com/watch?v=U-5ZhWqmUdI
    }
    public function getDynamicColumns123($languageCondition, $dynamicColumns)
    {
        $bc = Broker::where("language_code", $languageCondition[2])->leftJoin('translations', "translations.translationable_id", '=', 'brokers.id')
            ->orderBy("translations.property", "desc")->orderBy("translations.value", "desc")
            ->select('brokers.*', 'translations.property', 'translations.value')->get();
        return $bc;

        //https://www.youtube.com/watch?v=U-5ZhWqmUdI
    }

    public function borkerJsonFilter($jsonSting)
    {
        $jsonArray = json_decode($jsonSting);

        foreach ($jsonArray as $broker) {
            dd($broker);
        }
    }

    //https://dev.to/othmane_nemli/laravel-wherehas-and-with-550o
    //https://dev.to/othmane_nemli/laravel-wherehas-and-with-550o
}
