<?php

namespace Modules\Brokers\Services;

use App\Utilities\BaseQueryParser;
use Illuminate\Http\Request;

class BrokerOptionQueryParser extends BaseQueryParser
{
    protected $querySafeParams = [
        "language"=>['eq'],
        "columns"=>['in'],
        "all_columns"=>['eq'],
        "broker_type"=>['eq'],
        
     ];

     protected $validatorMap = [
        "all_columns"=>"boolean",
        "language" => "string|min:2|max:5",
        "columns" => "array|min:1",
        "broker_type"=>"string|min:3|max:10",
     ];

     protected $columnMap = [
        "language"=>"language_code",
     ];
}
