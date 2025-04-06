<?php

namespace Modules\Translations\Utilities;

use App\Utilities\BaseQueryParser;
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

