<?php

namespace Modules\Brokers\Services;

use App\Repositories\RepositoryInterface;
use App\Services\BaseQueryParser;
use Illuminate\Database\Eloquent\Collection;
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

        //dd( isset($queryParser->getAllFilters()["whereIn"]["filter_offices"]));
       
       // dd($queryParser->getAllFilters());
        /** @var  Modules\Brokers\Repositories\BrokerRepository $repo*/
        $repo=$this->repository;
     //   dd($queryParser->getWhereInParam("columns"));
        $columns=!empty($queryParser->getWhereInParam("columns"))?$queryParser->getWhereInParam("columns")[1]:null;

        $orderBy=!empty($queryParser->getOrderBy())?$queryParser->getOrderBy()[0]:null;
    
        return $repo->getDynamicColumns(
            $queryParser->getWhereParam("language"),
            $columns,
            $orderBy,
            $queryParser->getOrderDirection(),
            $queryParser->getAllFilters());
       
    }

   
}
