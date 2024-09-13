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

      
       $columns=$this->extractFromWhereInParams($queryParams,"columns");
       
       $offices=$this->extractFromWhereInParams($queryParams,"offices");
       $filters=[];
       if(!empty($offices))
       {
        $filters["offices"]=$offices;
       }
      
        /** @var  Modules\Brokers\Repositories\BrokerRepository $repo*/
        $repo=$this->repository;
    
        return $repo->getDynamicColumns($queryParams["language"],$columns,$queryParams["orderBy"],$queryParams["orderDirection"],$filters);
       
    }

    public function extractFromWhereInParams(array &$queryParams,string $field):array|null
    {
      
        foreach($queryParams["whereInParams"] as $k=>$v )
        {
            if($v[0]===$field)
            {
               $columns=$v[1];
                unset($queryParams["whereInParams"][$k]);
              // array_splice($queryParams["whereInParams"],$k,1);
                return $columns;
            }
        }

        return [];
    }
}
