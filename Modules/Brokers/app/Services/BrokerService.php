<?php

namespace Modules\Brokers\Services;

use App\Repositories\RepositoryInterface;
use App\Utilities\BaseQueryParser;
use Modules\Brokers\Models\Broker;
// use Illuminate\Database\Eloquent\Collection;
// use Modules\Brokers\Models\Zone;
// use Modules\Brokers\Transformers\BrokerCollection;
// use Modules\Translations\Repositories\TranslationRepository;
// use Modules\Translations\Models\Translation;
// use Modules\Translations\Transformers\TranslationCollection;
// use Modules\Brokers\Repositories\BrokerRepository;

class BrokerService
{

    public function __construct(
        protected RepositoryInterface $repository)
    {
    }
    //the repository is BrokerRepository which is bounded to the BrokerService
    //automatically injected Providers/BrokersServiceProvider.php

   
    public function process(BaseQueryParser $queryParser)
    {

        /** @var  Modules\Brokers\Repositories\BrokerRepository $repo*/
        $repo=$this->repository;
        $columns=!empty($queryParser->getWhereInParam("columns"))?$queryParser->getWhereInParam("columns")[1]:null;
        $orderBy=!empty($queryParser->getOrderBy())?$queryParser->getOrderBy()[0]:null;
        $zoneCondition= $queryParser->getWhereParam("zone");
        $languageCondition = $queryParser->getWhereParam("language");
       
        if(empty($zoneCondition) || empty($languageCondition)){
            return response()->json(['error' => 'Zone and language parameters are required'], 422);
        }
       
       
        return $repo->getDynamicColumns(
            $languageCondition,
            //change to zone code in production
          $zoneCondition,
            $columns,
            $orderBy,
            $queryParser->getOrderDirection(),
            $queryParser->getAllFilters()
        );
       
    }

    /**
     * Get broker context for a given broker id
     * @param int $id
     * @return array
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getBrokerContext(int $id):array
    {
        $broker = Broker::with([
            'brokerType',
            'country.zone',
            'dynamicOptionsValues' => function($query) {
                $query->where('option_slug', '=', 'trading_name')->whereNull('zone_code');
            }
        ])->findOrFail($id);
        
        return [
            'success' => true,
            'data' => [
                'broker_id' => $broker->id,
                 'broker_type' => $broker->brokerType->name,
                 'broker_trading_name' => $broker->dynamicOptionsValues->first()->value,
                 'country_id' => $broker->country->id,
                 'zone_id' => $broker->country->zone->id,
                'country_code' => $broker->country->country_code ?? null,
                'zone_code' => $broker->country->zone->zone_code ?? null,
            ],
        ];
    }



   
}
