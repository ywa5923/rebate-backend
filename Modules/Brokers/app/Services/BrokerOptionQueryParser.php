<?php

namespace Modules\Brokers\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class BrokerOptionQueryParser extends BaseQueryParser
{
    protected $querySafeParams = [
        "language"=>['eq'],
        "columns"=>['in'],
        
     ];
     protected $columnMap = [
        "language"=>"language_code",
       
     ];
}
