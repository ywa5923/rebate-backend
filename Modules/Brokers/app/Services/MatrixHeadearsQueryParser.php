<?php

namespace Modules\Brokers\Services;

use App\Utilities\BaseQueryParser;
use Illuminate\Http\Request;

class MatrixHeadearsQueryParser extends BaseQueryParser
{
    protected $querySafeParams = [
        "language"=>['eq'],
       // "type"=>['eq'],
        "broker_id"=>['eq'],
        "matrix_id"=>['eq'],
        "broker_id_strict"=>['eq'],
        
     ];

     protected $validatorMap = [
        
        "language" => "string|min:2|max:5",
       // "type" => "string|min:3|max:10",
        "broker_id"=>"string|min:1|max:45",
        "matrix_id"=>"string|min:1|max:45",
        "broker_id_strict"=>"boolean",
     ];

     protected $columnMap = [
        "language"=>"language_code",
        "matrix_id"=>"name",
     ];
}
