<?php

namespace Modules\Translations\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class ZoneQueryParser extends BaseQueryParser
{

  protected $querySafeParams = [
    'country' => ['eq'],
  
  ];

  protected $columnMap = [
    "country" => "country_code",
  ];

 
}

