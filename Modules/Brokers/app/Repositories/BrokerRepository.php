<?php

namespace Modules\Brokers\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\OptionValue;
use Modules\Brokers\Transformers\BrokerCollection;
use Modules\Translations\Models\Translation;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brokers\Models\BrokerOption;

class BrokerRepository implements RepositoryInterface
{

    const TRANSLATED_DYNAMIC = 'translated-dynamic';
    const TRANSLATED_STATIC = 'translated-static';
    const STATIC = "static";
    const DYNAMIC = "dynamic";
    use BrokerTrait;



    /**
     * Get dynamic columns from broker table, translated and filtered.
     * @param array $languageCondition - language condition array [language_code, language_id, lang]
     * @param array $columns - array of columns to return
     * @param string $orderBy - column to order by
     * @param string $orderDirection - direction of order
     * @param array $filters - array of filters
     * @return BrokerCollection - collection of broker data
     */

    public function getDynamicColumns($languageCondition, $zoneCondition, $dynamicColumns, $orderBy, $orderDirection, $filters)
    {

        //to be added in env.file or global params
        //$tableStaticColumns = ['home_url', 'user_rating', 'account_type', 'trading_name', 'overall_rating', 'support_options', 'account_currencies', 'trading_instruments'];
        $tableExtRelations = ['regulators'];
        // if (empty($columns))
        //     $columns = $tableStaticColumns;

        //  $dynamicColumns = array_diff($columns, array_merge($tableStaticColumns, $tableExtRelations));
        //  $selectedStaticColumns = array_intersect($tableStaticColumns, $columns);


        if (empty($dynamicColumns))
            $dynamicColumns = $this->getDynamicColumnsFromDB();
     
         
            array_unshift($dynamicColumns,'logo','home_url','trading_name'); 

          // dd($dynamicColumns);
       
        $selectedExtRelations = array_intersect($tableExtRelations, $dynamicColumns);

        $jsonResult = $this->makeQuery($languageCondition, $zoneCondition,  $dynamicColumns,   $selectedExtRelations, $orderBy, $orderDirection, $filters);

        return (new BrokerCollection($jsonResult,["dynamic_columns"=>$dynamicColumns]));

        //return $jsonResult;
    }

    public function getDynamicColumnsFromDB()
    {
        return BrokerOption::where([['for_brokers', '=', 1], ['default_loading', '=', '1']])
            ->orderBy('default_loading_position', 'asc')
            ->pluck("slug")->toArray();
    }


    /**
     * Build a query for retrieving broker data, with translations and dynamic options,
     * filtered and ordered according to the given parameters.
     *
     * @param array $languageCondition - language condition array [language_code, language_id, lang]
     * @param array $dynamicColumns - array of dynamic columns (option values) from broker table
     * @param array $extRelations - array of external relations (regulators, etc) to load
     * @param string $orderBy - column to order by
     * @param string $orderDirection - direction of order
     * @param array $filters - array of filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator - paginated result
     */

    public function makeQuery($languageCondition, $zoneCondition, $dynamicColumns, $extRelations, $orderBy, $orderDirection, $filters): LengthAwarePaginator
    {

        // dd($zoneCondition);
        DB::enableQueryLog();
        $qb = Broker::select(["id"])->with([
            // 'translations' => function (Builder $query) use ($languageCondition,$zoneCondition,) {
            //     /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            //     $query->where("language_code", $languageCondition[2])
            //     ->where(...$zoneCondition);
            // },
            'dynamicOptionsValues' => function (Builder $query) use ($dynamicColumns, $zoneCondition) {

                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->whereIn("option_slug", $dynamicColumns);
                $this->addZoneCondition($query, $zoneCondition);
            },
            'dynamicOptionsValues.translations' => function (Builder $query) use ($languageCondition, $zoneCondition) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where(...$languageCondition);
            }
        ]);



        if (!empty($extRelations)) {
            foreach ($extRelations as $relationName) {

                $qb->with([$relationName, $relationName . ".translations" => function (Builder $query) use ($languageCondition, $zoneCondition) {
                    /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                    $query->where("language_code", $languageCondition[2]);
                }]);
            }
        }


        if (isset($filters["whereIn"])) {
            $this->addWhereInFilters($qb, $filters["whereIn"], $languageCondition, $zoneCondition);
        }


        if (isset($filters["where"])) {
            $this->addWhereFilters($qb, $filters["where"], $languageCondition, $zoneCondition);
        }

        if (!empty($orderBy) && !empty($orderDirection)) {

         
            $orderQueryType = self::DYNAMIC;
            $translatedEntry = Translation::where(
                [$languageCondition, ['property', '=', $orderBy]]
            )->first();
            if (!is_null($translatedEntry)) {
                if ($translatedEntry->translationable_type == Broker::class) {
                    $orderQueryType = self::TRANSLATED_STATIC;
                }

                if ($translatedEntry->translationable_type == OptionValue::class) {
                    $orderQueryType = self::TRANSLATED_DYNAMIC;
                }
        }
        $this->addOrderBy($qb, $orderBy, $orderDirection, $languageCondition, $orderQueryType,$zoneCondition);
        
     }
    
        return $qb->paginate();
    }

    public function addZoneCondition(Builder $query, array $zoneCondition)
    {

        /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
        $query->where(function (Builder $query) use ($zoneCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where("option_values.zone_code",$zoneCondition[2])->orWhere('is_invariant', true);
        });
    }


    /**
     * @param Builder $queryBuilder
     * @param array $filters
     * @param array $languageCondition
     *
     * Add filters to the query builder. The filters array is structured like Eloquent where(). Example:["filter_min_deposit"=>["min_deposit",'<',100]]
     */

    public function addWhereFilters(Builder $queryBuilder, array $filters, array $languageCondition, array $zoneCondition): void
    {
        if (isset($filters["filter_min_deposit"])) {
            $this->addWhereDynamicOption($queryBuilder, $filters["filter_min_deposit"],$zoneCondition);
        }
    }


    /**
     * @param Builder $queryBuilder
     * @param array $filters
     * @param array $languageCondition
     * 
     * Apply WHERE IN filters to the query builder
     *
     * The filter array is structured like Eloquent whereIn(). Example:
     * [
     *     "filter_offices" => [
     *         0 => "offices",
     *         1 => ["Greece"]
     *     ],
     *     "filter_trading_instruments" => [
     *         0 => "trading_instruments",
     *         1 => ["Forex", "Stocks"]
     *     ],
     *     "filter_support_options" => [
     *         0 => "support_options",
     *         1 => ["Phone", "Email"]
     *     ],
     *     "filter_account_currency" => [
     *         0 => "account_currency",
     *         1 => ["USD", "EUR", "GBP"]
     *     ],
     *     "filter_headquarters" => [
     *         0 => "headquarters",
     *         1 => ["USA", "UK", "Germany"]
     *     ],
     *     "filter_withdrawal_methods" => [
     *         0 => "withdrawal_methods",
     *         1 => ["Credit/Debit Card", "PayPal", "Skrill"]
     *     ],
     *     "filter_group_trading_account_info" => [
     *         0 => "filter_group_trading_account_info",
     *         1 => [
     *             "trading_account_info_1" => true,
     *             "trading_account_info_2" => false,
     *             "trading_account_info_3" => true
     *         ]
     *     ],
     *     "filter_group_fund_managers_features" => [
     *         0 => "filter_group_fund_managers_features",
     *         1 => [
     *             "fund_managers_features_1" => true,
     *             "fund_managers_features_2" => false,
     *             "fund_managers_features_3" => true
     *         ]
     *     ]
     * ]
     *
     */

    public function addWhereInFilters(Builder $queryBuilder, array $filters, array $languageCondition, array $zoneCondition): void
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

        if (isset($filters["filter_regulators"])) {
            $this->addRegulatorWhereInFilters($queryBuilder, $filters["filter_regulators"], $zoneCondition);
        }
        if (isset($filters["filter_trading_instruments"])) {
           
            $this->addWhereLikeDynamicOption($queryBuilder, $filters["filter_trading_instruments"], $languageCondition, $zoneCondition);
        }
        if (isset($filters["filter_support_options"])) {
           
            $this->addWhereLikeDynamicOption($queryBuilder, $filters["filter_support_options"], $languageCondition, $zoneCondition);
        }
        if (isset($filters["filter_account_currency"])) {
           
            $this->addWhereLikeDynamicOption($queryBuilder, $filters["filter_account_currency"], NULL, $zoneCondition);
        }
        if (isset($filters["filter_withdrawal_methods"])) {
            $this->addWhereLikeDynamicOption($queryBuilder, $filters["filter_withdrawal_methods"], $languageCondition, $zoneCondition);
        }

        if (isset($filters["filter_offices"])) {
            $this->addCompanyWhereLikeFilters($queryBuilder, $filters["filter_offices"], $languageCondition, $zoneCondition);
        }
        if (isset($filters["filter_headquarters"])) {
            $this->addCompanyWhereLikeFilters($queryBuilder, $filters["filter_headquarters"], $languageCondition, $zoneCondition);
        }


        if (isset($filters["filter_group_trading_account_info"])) {
            [$fiteredField,$slugArray]=$filters["filter_group_trading_account_info"];
           
            $this->groupBooleanDynamicOptions($queryBuilder,$fiteredField, $slugArray, $languageCondition, $zoneCondition);
        }
        if (isset($filters["filter_group_fund_managers_features"])) {
            [$fiteredField,$slugArray]=$filters["filter_group_fund_managers_features"];
            $this->groupBooleanDynamicOptions($queryBuilder,$fiteredField,$slugArray, $languageCondition, $zoneCondition);
        }

        if (isset($filters["filter_mobile"])) {
          
            $this->addWhereLikeDynamicOption($queryBuilder, $filters["filter_mobile"], null, $zoneCondition);
        }
        if (isset($filters["filter_web"])) {
            $this->addWhereLikeDynamicOption($queryBuilder, $filters["filter_web"], null, $zoneCondition);
        }
    }



    /**
     * Adds a where has filter to the given query builder for broker regulators.
     *
     * The filter array should be in the format:
     * array:1 [
     *     "filter_regulators" => array:2 [
     *       0 => "regulators"
     *       1 => array:1 [
     *         0 => "Greece"
     *       ]
     *     ]
     *   ]
     *
     * @param Builder $queryBuilder
     * @param array $filters
     */

    public function addRegulatorWhereInFilters(Builder $queryBuilder, array $filters, array $zoneCondition): void
    {
        /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        $queryBuilder->whereHas('regulators', function ($query) use ($filters, $zoneCondition) {

            $query->where(function ($query) use ($zoneCondition) {
                $query->whereIn('broker_regulator.zone_code', [$zoneCondition[2]])->orWhere('broker_regulator.is_invariant', true);
            });

            $query->whereIn('abreviation', $filters[1]);
        })->withCount(['regulators as regulators_count' => function ($query) use ($filters) {
            $query->whereIn('abreviation', $filters[1]);
        }])
            ->having('regulators_count', '=', count($filters[1]));
    }



    /**
     * Adds a where like query to the given query builder for broker static culumns with translations.
     *
     * @param Builder $queryBuilder
     * @param string $columnName
     * @param array $filters
     * @param array $languageCondition
     * @param bool $translate
     *
     * If $translate is true and the language code in $languageCondition is not 'en',
     * the query will be translated.
     *
     * If $translate is false, the query will not be translated.
     */

    public function addWhereLikeStaticColumn(Builder $queryBuilder, string $columnName, array $filters, array $languageCondition, bool $translate = true): void
    {
        /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        if ($languageCondition[2] == 'en' || $translate == false) {
            foreach ($filters as $filter) {

                $queryBuilder->where($columnName, 'LIKE', '%' . $filter . '%');
            }
        } else {
            $queryBuilder->whereHas('translations', function (Builder $queryBuilder) use ($columnName, $filters, $languageCondition) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
                $queryBuilder->where("property", $columnName)->where("language_code", $languageCondition[2])->where(function (Builder $queryBuilder) use ($filters) {
                    /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
                    foreach ($filters as $filter) {
                        $queryBuilder->where("value", 'LIKE', '%' . $filter . '%');
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

    public function groupBooleanDynamicOptions(Builder $queryBuilder, string $filteredField,array $slugArray, array $languageCondition, array $zoneCondition): void
    {
      
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
            $queryBuilder->withCount(["dynamicOptionsValues as {$filteredField}" => function ($query) use ($slugArray, $zoneCondition) {

                $this->addZoneCondition($query, $zoneCondition);
                $query->where(function ($query) use ($slugArray) {
                    foreach ($slugArray as $slug) {

                        $query->orWhere(function ($query) use ($slug) {
                            $query->where("option_slug", $slug)->where("value", true);
                        });
                    }
                });
            }])->having($filteredField, '=', count($slugArray));       
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

    public function addCompanyWhereLikeFilters(Builder $queryBuilder, array $filters, array $languageCondition, array $zoneCondition): void
    {
 
        /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        if ($languageCondition[2] == "en") {
            $queryBuilder->whereHas('companies', function ($query) use ($filters, $zoneCondition) {

                $query->where(function ($query) use ($zoneCondition) {
                    $query->whereIn('broker_company.zone_code', [$zoneCondition[2]])->orWhere('broker_company.is_invariant', true);
                });


                $query->where(function ($query) use ($filters) {
                    foreach ($filters[1] as $filter) {
                        $query->where($filters[0], 'LIKE', "%" . $filter . "%");
                    }
                });
            });
        } else {

            $queryBuilder->whereHas('companies.translations', function ($query) use ($filters, $languageCondition, $zoneCondition) {

                $query->where(function ($query) use ($zoneCondition) {
                    $query->whereIn('broker_company.zone_code', [$zoneCondition[2]])->orWhere('broker_company.is_invariant', true);
                });
                $query->where(function ($query) use ($languageCondition, $filters) {
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

    public function addWhereDynamicOption(Builder $queryBuilder, array $whereCondition, array $zoneCondition): void
    {
        /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
       
            $queryBuilder->whereHas('dynamicOptionsValues', function ($query) use ($whereCondition, $zoneCondition) {
                $query->where("option_slug", $whereCondition[0])->where("value", $whereCondition[1], $whereCondition[2]);
                $this->addZoneCondition($query, $zoneCondition);

                //->whereRaw("CAST(SUBSTRING(value, REGEXP_INSTR(value, '[0-9]+')) AS UNSIGNED) {$whereCondition[1]} ?", [$whereCondition[2]]);

            });
        
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

    public function addWhereLikeDynamicOption(Builder $queryBuilder, array $whereInCondition, array|null $languageCondition, array $zoneCondition): void
    {
        /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */

        if ( is_null($languageCondition) || $languageCondition[2] == 'en' ) {

            $queryBuilder->whereHas('dynamicOptionsValues', function ($query) use ($whereInCondition, $zoneCondition) {
               
                $this->addZoneCondition($query, $zoneCondition);
                 $query->where("option_slug", $whereInCondition[0])->where(function ($query) use ($whereInCondition) {
                    foreach ($whereInCondition[1]  as $filter) {
                        $query->where("value", 'LIKE', "%" . $filter . "%");
                    }
                });
               
            });
        } else {
            
            $queryBuilder->whereHas('dynamicOptionsValues.translations', function ($query) use ($whereInCondition, $languageCondition, $zoneCondition) {
                $query->where("option_slug", $whereInCondition[0]);
                $this->addZoneCondition($query, $zoneCondition);
                $query->where(function ($query) use ($languageCondition, $whereInCondition, $zoneCondition) {
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


        // $this->dumpSql($queryBuilder);

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

    public function addOrderBy(Builder $queryBuilder, string $orderBy, string $orderDirection, array $languageCondition,  string $queryType,$zoneCondition): void
    {
       // dd($queryType);
        /** @var Illuminate\Contracts\Database\Eloquent\Builder   $queryBuilder */
        if ($queryType == self::TRANSLATED_DYNAMIC) {
            $queryBuilder->addSelect([
                $orderBy => Translation::select("translations.value")
                    ->join("option_values", function ($join) use ($languageCondition, $orderBy) {
                        $join->on("translations.translationable_id", "=", "option_values.id")
                            ->where("translations.translationable_type", '=', OptionValue::class)
                            ->where("translations.language_code", "=", $languageCondition[2])
                            ->where("translations.property", "=", $orderBy);
                    })->whereColumn("option_values.broker_id", '=', "brokers.id")
                    ->where("option_values.option_slug", "=", $orderBy)
                    ->where(function (Builder $query) use ($zoneCondition) {
                        /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                        $query->where("option_values.zone_code",$zoneCondition[2])->orWhere('is_invariant', true);
                    })

            ])->orderBy($orderBy, $orderDirection);
                
        }

        
       

        if ($queryType == self::DYNAMIC) {
            //DB::raw('CAST(value AS UNSIGNED) AS value')
            $queryBuilder->addSelect([$orderBy => OptionValue::select('value')->whereColumn("option_values.broker_id", '=', "brokers.id")
                ->where("option_values.option_slug", "=", $orderBy)
                ->where(function (Builder $query) use ($zoneCondition) {
                    /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                    $query->where("option_values.zone_code",$zoneCondition[2])->orWhere('is_invariant', true);
                })
            
            ])
        
           ->orderByRaw("cast(concat('0', {$orderBy})+0 as char)={$orderBy} {$orderDirection}, 0+{$orderBy} {$orderDirection}, {$orderBy} {$orderDirection}");
   
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
        //  $queryBuilder->toSql();
        dd($this->getFullSql($queryBuilder));
        //,DB::getQueryLog()
        //,$queryBuilder->getBindings()

    }

    public function getFullSql($query)
    {
        $sql = $query->toSql();
        foreach ($query->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'$binding'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }

    //https://dev.to/othmane_nemli/laravel-wherehas-and-with-550o
    //https://dev.to/othmane_nemli/laravel-wherehas-and-with-550o
}

//http://localhost:8000/api/v1/brokers?language[eq]=ro&page=1&filter_group_trading_account_info[in]=islamic_accounts,1_click_trading,allow_expert_advisors&filter_group_fund_managers_features[in]=mam_pamm_leaderboards&filter_account_currency[in]=AUD&zone[eq]=zone1