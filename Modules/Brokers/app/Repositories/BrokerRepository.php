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


    public function getDynamicColumns($languageCondition, $columns, $orderBy, $orderDirection, $filters)
    {

        //to be added in env.file or global params
        $tableStaticColumns = ['home_url', 'user_rating', 'account_type', 'trading_name', 'overall_rating', 'support_options', 'account_currencies', 'trading_instruments'];
        $tableExtRelations = ['regulators'];
        if (empty($columns))
            $columns = $tableStaticColumns;


        $dynamicColumns = array_diff($columns, array_merge($tableStaticColumns, $tableExtRelations));
        $selectedStaticColumns = array_intersect($tableStaticColumns, $columns);
        $selectedExtRelations = array_intersect( $tableExtRelations, $columns);

        if (empty($selctedStaticColumns) && empty($dynamicColumns))
            $selctedStaticColumns = $tableStaticColumns;

        $jsonResult = $this->makeQuery($languageCondition,  $selectedStaticColumns, $dynamicColumns,   $selectedExtRelations, $orderBy, $orderDirection, $filters);

        return new BrokerCollection($jsonResult);

        //return $jsonResult;
    }
    public function makeQuery($languageCondition, $staticColumns, $dynamicColumns, $extRelations, $orderBy, $orderDirection, $filters)
    {
       
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
        if (!empty($extRelations)) {
            foreach ($extRelations as $relationName) {

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
      
        if (!empty($orderBy) && !empty($orderDirection)) {
           
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

            $this->addOrderBy($bc, $orderBy, $orderDirection, $languageCondition, $orderQueryType);
        }
        $bc=$bc->paginate();
        return $bc;
    }

    public function addWhereFilters(Builder $queryBuilder,array $filters, array $languageCondition):void {
        if(isset($filters["filter_min_deposit"])){
            $this->addWhereDynamicOption($queryBuilder,$filters["filter_min_deposit"]);
        }
    }

    public function addWhereInFilters(Builder $queryBuilder, array $filters, array $languageCondition):void
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

     if(isset($filters["filter_regulators"]))
     {
        $this->addRegulatorWhereInFilters($queryBuilder,$filters["filter_regulators"]);

     }
     if(isset($filters["filter_trading_instruments"]))
     {
        $this->addWhereLikeStaticColumn($queryBuilder,$filters["filter_trading_instruments"][0],$filters["filter_trading_instruments"][1],$languageCondition);
     }
     if(isset($filters["filter_support_options"])){
        $this->addWhereLikeStaticColumn($queryBuilder,$filters["filter_support_options"][0],$filters["filter_support_options"][1],$languageCondition);
     }
     if(isset($filters["filter_account_currency"]))
     {
        $this->addWhereLikeStaticColumn($queryBuilder,$filters["filter_account_currency"][0],$filters["filter_account_currency"][1],$languageCondition,false);
       
     }

     if(isset($filters["filter_offices"])){
        $this-> addCompanyWhereLikeFilters($queryBuilder, $filters["filter_offices"], $languageCondition);
     }
     if(isset($filters["filter_headquarters"])){
        $this-> addCompanyWhereLikeFilters($queryBuilder, $filters["filter_headquarters"], $languageCondition);
     }

     if(isset($filters["filter_withdrawal_methods"]))
     {
        $this->addWhereLikeDynamicOption($queryBuilder,$filters["filter_withdrawal_methods"],$languageCondition);
     }

     if(isset($filters["filter_group_trading_account_info"]))
     {
        $this->groupBooleanDynamicOptions($queryBuilder,$filters["filter_group_trading_account_info"][1]);
     }
     if(isset($filters["filter_group_fund_managers_features"]))
     {
        $this->groupBooleanDynamicOptions($queryBuilder,$filters["filter_group_fund_managers_features"][1]);
     }

    }

    public function addRegulatorWhereInFilters(Builder $queryBuilder,array $filters):void
    {
         /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        $queryBuilder->whereHas('regulators', function ($query) use ($filters) {
           
            $query->whereIn('abreviation', $filters[1]);

        }) ->withCount(['regulators as regulators_count' => function ($query) use ($filters)  {
            $query->whereIn('abreviation', $filters[1]);
        }])
        ->having('regulators_count', '=', count($filters[1]));
    }

    public function addWhereLikeStaticColumn(Builder $queryBuilder,string $columnName, array $filters,array $languageCondition,bool $translate=true):void
    {
         /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        if($languageCondition[2]=='en' || $translate==false){
            foreach($filters as $filter){
         
                $queryBuilder->where($columnName,'LIKE','%'.$filter.'%');
                
              }
        }else{
            $queryBuilder->whereHas('translations',function(Builder $queryBuilder) use ($columnName,$filters,$languageCondition) {
                 /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
                $queryBuilder->where("property",$columnName)->where("language_code",$languageCondition[2])->where(function(Builder $queryBuilder) use ($filters){
                  /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
                    foreach($filters as $filter){
                        $queryBuilder->where("value",'LIKE','%'.$filter.'%');
                    }
                });
            });
        }
       
    }

   


    /**
     * Filters brokers by dynamic options with option_slug in given slug array.
     * All dynamic options values with given slug should be true.
     * @param Builder $queryBuilder
     * @param array $slugArray
     */

    public function groupBooleanDynamicOptions(Builder $queryBuilder, array $slugArray):void
    {
      /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
      $queryBuilder->withCount(['dynamicOptionsValues as matching_options_count' => function ($query) use ($slugArray) {
     
            $query->where(function ($query) use ($slugArray) {
                foreach ($slugArray as $slug) {
               
                $query->orWhere(function ($query) use ($slug){
                       $query->where("option_slug", $slug)->where("value", true);
                });
            }
            });
        
    }])
    ->having('matching_options_count', '=', count($slugArray));
    }



    /**
     * @param Builder $queryBuilder
     * @param array $filters
     * @param array $languageCondition
     * 
     * This function adds a whereHas filter to the queryBuilder for companies with offices
     * or headquarters in given countries.
     * Example of the $filters array:  
     *  [
     *     0 => "offices"
     *     1 => array:2 [
     *       0 => "Cyprus"
     *       1 => "Romania"
     *     ]
     *   ]
     * 
     */

    public function addCompanyWhereLikeFilters(Builder $queryBuilder, array $filters, array $languageCondition)
    {
         /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        if ($languageCondition[2] == "en") {
                 $queryBuilder->whereHas('companies', function ($query) use ($filters) {
                    foreach ($filters[1] as $filter) {
                            $query->where($filters[0], 'LIKE', "%" . $filter . "%");
                    }
                });
          
        } else{
           
         $queryBuilder->whereHas('companies.translations', function ($query) use ($filters, $languageCondition) {
                   
                    $query->where(function ($query) use ($languageCondition,$filters) {
                        $query->where("translations.property", '=', $filters[0]);
                        $query->where("translations.language_code", '=', $languageCondition[2]);
                    })->where(function ($query) use ($filters) {
                       
                        foreach ($filters[1]  as $filter) {
                                $query->where("translations.value", 'LIKE', "%" . $filter . "%");
                            
                        }
                    });
                });

        }
        //$this->dumpSql($queryBuilder);
    }

    /**
     * @param Builder $queryBuilder
     * @param array $whereCondition
     * @param bool $translate
     * $return void
     * Adds a whereHas filter to the queryBuilder for brokers with a given dynamic option value.
     * The $whereCondition array should contain the option slug and the comparison operator and value.
     * Example: ["min_deposit","<",100]
     * If translate is set to true, then the $whereCondiion will be applied to translations table
     */

    public function addWhereDynamicOption(Builder $queryBuilder,array $whereCondition,bool $translate=null):void
    {
      /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        if(is_null($translate)){
            $queryBuilder->whereHas('dynamicOptionsValues', function ($query) use ($whereCondition) {
                $query->where("option_slug",$whereCondition[0])->where("value",$whereCondition[1],$whereCondition[2]);
              
               //->whereRaw("CAST(SUBSTRING(value, REGEXP_INSTR(value, '[0-9]+')) AS UNSIGNED) {$whereCondition[1]} ?", [$whereCondition[2]]);
               
            });
        }
    }


    /**
     * @param Builder $queryBuilder
     * @param array $whereInCondition
     * @param array $languageCondition
     * 
     * Example of whereInCondition
     *   [
     *     0 => "withdrawal_methods"
     *     1 => array:2 [
     *       0 => "Bank of Valletta"
     *       1 => "Baltikums Bank"
     *     ]
     *   ]
     * 
     * This function will add a where condition to the query builder
     * by using  the dynamic_options_values table and  the translations table 
     * depending on the languageCondition. It will filter the results
     * by the value of the dynamic option value or the translated value using LIKE operator in the where condition
     * If the languageCondition is english, the query will be done on the dynamic_options_values table
     * If the languageCondition is not english, the query will be done on the translations table
     * 
     * @return void
     */

    public function addWhereLikeDynamicOption(Builder $queryBuilder,array $whereInCondition,array $languageCondition)
    {
       /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */

        if($languageCondition[2]=='en'){
             
            $queryBuilder->whereHas('dynamicOptionsValues', function ($query) use ($whereInCondition) {
                $query->where("option_slug",$whereInCondition[0])->where(function ($query) use ($whereInCondition) {
                    foreach ($whereInCondition[1]  as $filter) {
                        $query->where("value", 'LIKE', "%" . $filter . "%");
                    }
                });
            });
        }else{
            $queryBuilder->whereHas('dynamicOptionsValues.translations', function ($query) use ($whereInCondition,$languageCondition) {
                $query->where(function ($query) use ($languageCondition,$whereInCondition) {
                    $query->where("property", '=', $whereInCondition[0]);
                    $query->where(...$languageCondition);
                })->where(function ($query) use ($whereInCondition) {
                    $index = 0;
                    foreach ($whereInCondition[1]  as $filter) {
                        $query->where("value", 'LIKE', "%" . $filter . "%");
                    }
                });
            });
        }

      //$this->dumpSql($queryBuilder);

    }

    /**
     * @param Builder $queryBuilder
     * @param string $orderBy
     * @param string $orderDirection
     * @param array $languageCondition
     * @param string $queryType
     * @return void
     * Adds an order by clause to the query builder.
     * 
     * Depending on the queryType, the order by will be done on a different table.
     * 
     * If queryType is "translated-dynamic", the order by will be done on the translations table,
     * joined with the option_values table.
     * 
     * If queryType is "translated-static", the order by will be done on the translations table,
     * joined with the brokers table.
     * 
     * If queryType is "static", the order by will be done on the brokers table without translations.
     * 
     * If queryType is "dynamic", the order by will be done on the option_values table without translations.
     * 
     */

    public function addOrderBy(Builder $queryBuilder, string $orderBy, string $orderDirection, array $languageCondition,  string $queryType):void
    {
         /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        if ($queryType == "translated-dynamic") {
             $queryBuilder->addSelect([
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

           $queryBuilder->addSelect([
                $orderBy => Translation::select("translations.value")
                    ->whereColumn("translations.translationable_id", "=", "brokers.id")
                    ->where("translations.language_code", "=", $languageCondition[2])
                    ->where("translations.property", "=", $orderBy)
                    ->where("translationable_type", "=", Broker::class)
            ])
                ->orderBy($orderBy, $orderDirection);
        }

        if ($queryType == 'static') {
             $queryBuilder->orderBy($orderBy, $orderDirection);
        }

        if ($queryType == 'dynamic') {

            $queryBuilder->addSelect([$orderBy => OptionValue::select("value")->whereColumn("option_values.broker_id", '=', "brokers.id")
                ->where("option_values.option_slug", "=", $orderBy)])
                ->orderBy($orderBy, $orderDirection);
        }
    }
   

    /**
     * @param Builder $queryBuilder
     * @return void
     * Dump the SQL query, bindings and the query log.
     *
     */

    public function dumpSql(Builder $queryBuilder)
    {
         /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        dd($queryBuilder->toSql(),$queryBuilder->getBindings());
        dd(DB::getQueryLog());
    }

    //https://dev.to/othmane_nemli/laravel-wherehas-and-with-550o
    //https://dev.to/othmane_nemli/laravel-wherehas-and-with-550o
}
