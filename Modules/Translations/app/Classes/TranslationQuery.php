<?php

namespace Modules\Translations\Classes;

use App\Classes\BaseQuery;
use Illuminate\Http\Request;

class TranslationQuery extends BaseQuery
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

  protected $operatorMap = [
    'eq' => '=',
    'lt' => '<',
    'lte' => '<=',
    'gt' => '>',
    'gte' => '>=',
    'in' => 'IN'

  ];

  protected $modelClassMap = [
    "Broker" => "Modules\Brokers\Models\Broker",
    "BrokerOption" => "Modules\Brokers\Models\BrokerOption"
  ];

 
}
