<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Models\Company;
use Modules\Brokers\Transformers\CompanyCollection;
use Modules\Brokers\Repositories\CompanyUniqueListInterface;

class CompanyRepository
{
   
    public function getUniqueList(array $langaugeCondition ,string $fieldName):array
    {
        $results=[];
        $this->getCompaniesWithTranslationsQB($langaugeCondition)->chunk(100,function ($companies) use (&$results,$fieldName){
              $list=[];
              $companyCollection=new CompanyCollection($companies);
              foreach($companyCollection->resolve() as $company)
              {
                array_push($list,...explode(",",$company[$fieldName]));
              }
              array_push($results,...array_unique($list));
         });

         return array_unique($results);
    }

    public function getCompaniesWithTranslationsQB($langaugeCondition)
    {
        return Company::with(["translations"=>function (Builder $query)use ($langaugeCondition){
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
             $query->where(...$langaugeCondition);
         }]);
    }
}