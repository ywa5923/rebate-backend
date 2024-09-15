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
       "filter_offices"=>['in']
    ];
  
    protected $columnMap = [
       "language"=>"language_code",
       "filter_offices"=>"offices"
      
    ];

    public function parse(Request $request)
    {
      return parent::parse($request);
      
    }
    

   
}

