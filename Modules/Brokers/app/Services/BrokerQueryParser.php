<?php

namespace Modules\Brokers\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class BrokerQueryParser extends BaseQueryParser
{
    protected $querySafeParams = [
       "language"=>['eq'],
       "country"=>["eq"],
       "tab"=>["eq"]
    ];
  
    protected $columnMap = [
       "language"=>"language_code",
    ];

}

