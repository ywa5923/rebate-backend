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

   
    public function test()
    {
        
    }

    public function process(array $queryParams)
    {

              
        /** @var  Modules\Brokers\Repositories\BrokerRepository $repo*/
        $repo=$this->repository;
        return $repo->getFullProfile($queryParams["language"]);
         
     

        $queryBuilder = Translation::query();
       

        return new BrokerCollection($queryBuilder->get());
    }
}
