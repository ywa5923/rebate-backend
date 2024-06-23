<?php

namespace Modules\Translations\Services;

use Illuminate\Http\Request;

class TranslationQuery
{

  protected $querySafeParams = [
    'model' => ['eq'],
    'properties' => ['eq'],
    'property' => ['in'],
    'lang' => ['eq'],
    'translation_type' => ['eq']

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

  public function transform(Request $request): array
  {

    //['column','operator','value']]
    $whereParams = [];
    //['column',['valuesArray']]
    $whereInParams = [];
    foreach ($this->querySafeParams as $param => $operators) {
      $query = $request->query($param);
      if (!isset($query))
        continue;

      $tableColumn = $this->columnMap[$param] ?? $param;

      foreach ($operators as $operator) {

        $paramValue = ($param === "model") ? ($this->modelClassMap[$query[$operator]]) : ($query[$operator]);

        if (isset($query[$operator])) {
          if ($operator === 'in') {
            $whereInParams[] = [$tableColumn, explode(',',   $paramValue)];
          } else {
            $whereParams[] = [$tableColumn, $this->operatorMap[$operator],   $paramValue];
          }
        }
      }
    }

    return [
      "whereParams" =>   $whereParams,
      "whereInParams" =>  $whereInParams
    ];
  }
}
