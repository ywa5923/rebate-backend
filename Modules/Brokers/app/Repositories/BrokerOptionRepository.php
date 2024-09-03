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
        return BrokerOption::without(["translations"])->get();
    }
    public function translate($langCondition)
    {
       //$langCondition=["language_code","=","ro"];
     return ($langCondition[2]=="eng")? $this->translateDefaultLanguage():$this->translateByLanguageCode($langCondition);
       

    }

    public function translateByLanguageCode($langCondition)
    {
        return BrokerOption::with(["translations"=>function (Builder $query) use ($langCondition){
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where(...$langCondition);
       }])->get();
    }
}