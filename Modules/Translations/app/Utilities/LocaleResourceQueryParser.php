<?php

namespace Modules\Translations\Utilities;

use App\Utilities\BaseQueryParser;
use Illuminate\Http\Request;

class LocaleResourceQueryParser extends BaseQueryParser
{

  protected $querySafeParams = [
    'key' => ['eq'],
    'section' => ['in','eq'],
    'lang' => ['eq'],
    'zone' => ['eq']
  ];

  protected $columnMap = [
    "zone" => "zone_code",
    "lang" => "language_code"
  ];

 
}
