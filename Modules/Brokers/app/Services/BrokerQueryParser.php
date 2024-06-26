<?php

namespace Modules\Brokers\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class BrokerQueryParser extends BaseQueryParser
{
    protected $querySafeParams = [
       "language"=>['eq'],
       "order_by"=>['eq']
    ];
  
    protected $columnMap = [
       "language"=>"language_code"
    ];

    public function parse(Request $request):array
    {
      $initialParsed=parent::parse($request);
     return $this->extractLanguage($initialParsed);
     
      
    }
    

    public function extractLanguage(array $paramsArray):array
    {
      $languageParams=[];

      foreach($paramsArray["whereParams"] as $k=>$v){
         if(in_array("language_code",$v)){
            $languageParams=$v;
            unset($paramsArray["whereParams"][$k]);
         }
      }

       $paramsArray["language"]= $languageParams;

       return $paramsArray;

    }
}

