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

    public function getDynamicColumns($languageCondition, $columns, $orderBy, $orderDirection,$filters)
    {

        //to be added in env.file or global params
        $tableStaticColumns = ['home_url', 'user_rating', 'account_type', 'trading_name', 'overall_rating', 'support_options', 'account_currencies', 'trading_instruments'];
        if (empty($columns))
            $columns = $tableStaticColumns;

        $dynamicColumns = array_diff($columns, $tableStaticColumns);
        $selctedStaticColumns = array_intersect($tableStaticColumns, $columns);

        if (empty($selctedStaticColumns) && empty($dynamicColumns))
            $selctedStaticColumns = $tableStaticColumns;

        $jsonResult = $this->getQueryJson($languageCondition, $selctedStaticColumns, $dynamicColumns, $orderBy, $orderDirection,$filters);

        

        //return new BrokerCollection($jsonResult);

        return $jsonResult;
    }
    public function getQueryJson($languageCondition, $staticColumns, $dynamicColumns, $orderBy, $orderDirection,$filters)
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
            $orderQueryType = in_array($orderBy[0], $tableStaticColumns) ? "static" : "dynamic";
            $translatedEntry = Translation::where(
                [$languageCondition, ['property', '=', $orderBy[0]]]
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

    public function addOrderBy($queryBuilder, $orderBy, $orderDirection, $languageCondition, $queryType)
    {
        if ($queryType == "translated-dynamic") {
            return $queryBuilder->addSelect([
                $orderBy[0] => Translation::select("translations.value")
                    ->join("option_values", function ($join) use ($languageCondition, $orderBy) {
                        $join->on("translations.translationable_id", "=", "option_values.id")
                            ->where("translations.translationable_type", '=', OptionValue::class)
                            ->where("translations.language_code", "=", $languageCondition[2])
                            ->where("translations.property", "=", $orderBy[0]);
                    })->whereColumn("option_values.broker_id", '=', "brokers.id")->where("option_values.option_slug", "=", $orderBy[0])

            ])
                ->orderBy($orderBy[0], $orderDirection);
        }

        if($queryType=="translated-static"){
           
            return $queryBuilder->addSelect([
                $orderBy[0]=>Translation::select("translations.value")
            ->whereColumn("translations.translationable_id","=","brokers.id")
            ->where("translations.language_code","=",$languageCondition[2])
            ->where("translations.property","=",$orderBy[0])
            ->where("translationable_type","=",Broker::class)])
            ->orderBy($orderBy[0], $orderDirection);
        }

        if ($queryType == 'static') {
            return $queryBuilder->orderBy($orderBy[0], $orderDirection);
        }

        if ($queryType == 'dynamic') {

            return $queryBuilder->addSelect([$orderBy[0] => OptionValue::select("value")->whereColumn("option_values.broker_id", '=', "brokers.id")
                ->where("option_values.option_slug", "=", $orderBy[0])])
                ->orderBy($orderBy[0], $orderDirection);
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
