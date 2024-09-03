<?php

namespace Modules\Brokers\Services;

use App\Repositories\RepositoryInterface;
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

   
    public function process(array $queryParams)
    {

      
       $columns=$this->extractDynamicColumns($queryParams);
       
      
        /** @var  Modules\Brokers\Repositories\BrokerRepository $repo*/
        $repo=$this->repository;
    
        return $repo->getDynamicColumns($queryParams["language"],$columns,$queryParams["orderBy"],$queryParams["orderDirection"]);
       
    }

    public function extractDynamicColumns(array $queryParams):array|null
    {
      
        foreach($queryParams["whereInParams"] as $k=>$v )
        {
            if($v[0]==="columns")
            {
               
                unset($queryParams["whereInParmas"][$k]);
                return $v[1];
            }
        }

        return [];
    }
}
