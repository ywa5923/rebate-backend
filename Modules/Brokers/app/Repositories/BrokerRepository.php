<?php
namespace Modules\Brokers\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Transformers\BrokerCollection;

class BrokerRepository implements RepositoryInterface
{
    use BrokerTrait;

    public function getFullProfile($languageCondition)
    {
        //dd($languageCondition);
        $bc=new BrokerCollection(Broker::with(['translations'=>function (Builder $query) use ($languageCondition){
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where("language_code",$languageCondition[2]);
        },'dynamicOptionsValues.translations'=> function (Builder $query) use ($languageCondition) {
           /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where("language_code",$languageCondition[2]);
         }])->paginate());

      return $bc;

     // $this->borkerJsonFilter($bc->toJson());

    
    }

    public function borkerJsonFilter($jsonSting)
    {
        $jsonArray=json_decode($jsonSting);

        foreach($jsonArray as $broker)
        {
           dd($broker);
        }
    }

    //https://dev.to/othmane_nemli/laravel-wherehas-and-with-550o
}