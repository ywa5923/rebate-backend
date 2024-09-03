<?php

namespace Modules\Brokers\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class BrokerQueryParser extends BaseQueryParser
{
    protected $querySafeParams = [
       "language"=>['eq'],
       "order_by"=>['eq'],
       "order_direction"=>['eq'],
       "columns"=>['in'],
       "filters"=>['in']
    ];
  
    protected $columnMap = [
       "language"=>"language_code",
      
    ];

    public function parse(Request $request):array
    {
      $initial=parent::parse($request);
    
      //extract language from whereParams and put it in array['langugage'] 
     return $this->extractLanguage( $initial);
     
      
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

