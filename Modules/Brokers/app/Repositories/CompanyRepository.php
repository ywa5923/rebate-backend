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
                $items=explode(",",$company[$fieldName]);
                foreach($items as $item){
                   if(!array_key_exists(trim($item),$list) && trim($item)!=="")
                   $list[trim($item)]=trim($item);
                }
                
              }
             
              $results= array_merge($results,$list);
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