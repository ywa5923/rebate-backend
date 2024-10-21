<?php
namespace Modules\Brokers\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Models\BrokerOption;

class BrokerOptionRepository implements RepositoryInterface
{
    use BrokerTrait;

    public function translateDefaultLanguage($lang="eng")
    {
        return BrokerOption::without(["translations"])
        ->where(function ($query){
            $query->where("for_brokers",1);
        })
        ->where("load_in_dropdown",1)->
        orWhere("default_loading",1)
        ->orderBy("default_loading","asc")
        ->get();
    }
    public function translate($langCondition)
    {
       //$langCondition=["language_code","=","ro"];
     return ($langCondition[2]=="en")? $this->translateDefaultLanguage():$this->translateByLanguageCode($langCondition);
       

    }

    public function translateByLanguageCode($langCondition)
    {
        return BrokerOption::with(["translations"=>function (Builder $query) use ($langCondition){
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where(...$langCondition);
       }])
        ->where(function ($query){
        $query->where("for_brokers",1);
        })
        ->where(function ($query){
            $query->where("load_in_dropdown",1)
            ->orWhere("default_loading",1);
        })->orderBy("default_loading","asc")
        ->get();
       
       
    }
}