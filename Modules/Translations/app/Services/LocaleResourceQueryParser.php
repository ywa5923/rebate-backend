<?php

namespace Modules\Translations\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class LocaleResourceQueryParser extends BaseQueryParser
{

  protected $querySafeParams = [
    'key' => ['eq'],
    'group' => ['eq'],
    'section' => ['in','eq'],
    'lang' => ['eq'],
    'zone' => ['eq'],
    
  ];

  protected $columnMap = [
    "zone" => "zone_code",
    "lang" => "language_code"
  ];

 
}
