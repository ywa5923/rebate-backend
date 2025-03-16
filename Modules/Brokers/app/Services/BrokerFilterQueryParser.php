<?php

namespace Modules\Brokers\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class BrokerFilterQueryParser extends BaseQueryParser
{
    protected $querySafeParams = [
       "language"=>['eq'],
       "zone"=>["eq"]
    ];
  
    protected $columnMap = [
       "language"=>"language_code",
       "zone"=>"zone_code"
     
      
    ];

}

