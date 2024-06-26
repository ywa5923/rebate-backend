<?php

namespace Modules\Translations\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class TranslationQueryParser extends BaseQueryParser
{

  protected $querySafeParams = [
    'model' => ['eq'],
    'properties' => ['eq'],
    'property' => ['in'],
    'lang' => ['eq'],
    'translation_type' => ['eq'],
    'order_by' => ['eq']
  ];

  protected $columnMap = [
    "model" => "translationable_type",
    "lang" => "language_code"
  ];



  protected $modelClassMap = [
    "Broker" => "Modules\Brokers\Models\Broker",
    "BrokerOption" => "Modules\Brokers\Models\BrokerOption"
  ];

 
}
