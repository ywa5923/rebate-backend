<?php

namespace Modules\Brokers\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class BrokerQueryParser extends BaseQueryParser
{
    protected $querySafeParams = [
       "language"=>['eq'],
       "zone"=>["eq"],
       "tab"=>["eq"]
    ];
  
    protected $columnMap = [
       "language"=>"language_code",
       "zone"=>"zone_code",
    ];

}

