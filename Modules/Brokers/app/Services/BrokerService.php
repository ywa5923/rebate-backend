<?php

namespace Modules\Brokers\Services;

use App\Repositories\RepositoryInterface;
use App\Services\BaseQueryParser;
use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\Zone;
use Modules\Brokers\Transformers\BrokerCollection;
use Modules\Translations\Repositories\TranslationRepository;
use Modules\Translations\Models\Translation;
use Modules\Translations\Transformers\TranslationCollection;
use Modules\Brokers\Repositories\BrokerRepository;

class BrokerService
{

    public function __construct(
        protected RepositoryInterface $repository)
    {
    }

   
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
        $zone=$zoneCondition[2];
 
        return $repo->getDynamicColumns(
            $languageCondition,
            //change to zone code in production
            ["zone_code","=","zone1"],
            $columns,
            $orderBy,
            $queryParser->getOrderDirection(),
            $queryParser->getAllFilters()
        );
       
    }

   
}
