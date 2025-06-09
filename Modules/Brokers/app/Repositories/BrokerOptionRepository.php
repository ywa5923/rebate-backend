<?php
namespace Modules\Brokers\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;

use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\OptionCategory;
use Illuminate\Support\Collection;
class BrokerOptionRepository implements RepositoryInterface
{
    use BrokerTrait;

    public function getDropdownOptionsDefaultLanguage($lang="en"):Collection
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
    public function getDropdownOptions($langCondition):Collection
    {
       //$langCondition=["language_code","=","ro"];
    // return ($langCondition[2]=="en")? $this->getDropdownOptionsDefaultLanguage():$this->getDropdownOptionsByLanguageCode($langCondition);
       
      return $this->getDropdownOptionsByLanguageCode($langCondition);
    }

    public function getDropdownOptionsByLanguageCode($langCondition):Collection
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

    public function getBrokerOptions(array $langCondition,int $brokerId, ?string $brokerType = null): Collection
    {
        return OptionCategory::with([
           
            'options' => function (Builder $query) use ($brokerType) {
                  /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                match ($brokerType) {
                    'props'   => $query->where('for_props', 1),
                    'brokers' => $query->where('for_brokers', 1),
                    'crypto'  => $query->where('for_crypto', 1),
                    default   => null, // No additional filtering
                };
    
                $query->orderBy('category_position', 'asc');
            },
            'options.translations' => function (Builder $query) use ($langCondition) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where(...$langCondition);
            },
            'options.values' => function (Builder $query) use ($brokerId) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where("broker_id",$brokerId);
            },
            'translations'         => function (Builder $query) use ($langCondition) {
                /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
                $query->where(...$langCondition);
            },
        ])
        ->orderBy('position', 'asc')
        ->get();
    }
    

    
}