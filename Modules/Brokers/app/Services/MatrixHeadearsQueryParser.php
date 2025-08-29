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
        "col_group"=>['eq'],
        "row_group"=>['eq'],
        "language"=>['eq'],
     ];

     protected $validatorMap = [
        
        "language" => "string|min:2|max:5",
       // "type" => "string|min:3|max:10",
        "broker_id"=>"string|min:1|max:45",
        "matrix_id"=>"string|min:1|max:45",
        "broker_id_strict"=>"boolean",
        "col_group"=>"string|min:1|max:45",
        "row_group"=>"string|min:1|max:45",
        "language"=>"string|min:2|max:5",
     ];

     protected $columnMap = [
        "language"=>"language_code",
        "matrix_id"=>"name",
        "col_group"=>"group_name",
        "row_group"=>"group_name",
        "language"=>"language_code",
     ];
}
