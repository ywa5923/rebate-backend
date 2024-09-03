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
//=========================Example of query parser result=================================
// array:4 [ // Modules/Brokers/app/Http/Controllers/BrokerOptionController.php:21
//     "whereParams" => array:1 [
//       0 => array:3 [
//         0 => "language_code"
//         1 => "="
//         2 => "ro"
//       ]
//     ]
//     "whereInParams" => array:1 [
//       0 => array:2 [
//         0 => "columns"
//         1 => array:2 [
//           0 => "colOne"
//           1 => "colTwo"
//         ]
//       ]
//     ]
//     "orderBy" => []
//     "orderDirection" => ""
//   ]