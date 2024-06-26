<?php

namespace App\Services;

use Illuminate\Http\Request;

class BaseQueryParser
{
    protected $querySafeParams = [
       
      ];
    
      protected $columnMap = [
      
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
       
      ];
    
      public function parse(Request $request): array
      {
    
        //['column','operator','value']]
        $whereParams = [];
        //['column',['valuesArray']]
        $whereInParams = [];
        $orderBy = [];
        $orderDirection = "";
        foreach ($this->querySafeParams as $param => $operators) {
          $query = $request->query($param);
          if (!isset($query))
            continue;
    
          $tableColumn = $this->columnMap[$param] ?? $param;
    
          foreach ($operators as $operator) {
            if (!isset($query[$operator]))
              continue;
    
            if ($param === "order_by") {
              $orderByString = $query[$operator];
              if ($orderByString[0] === '-') {
                $orderDirection = 'DESC';
                $orderByString=ltrim($orderByString,'-');
    
              } else {
                $orderDirection = 'ASC';
              }
    
              $orderBy= explode(',', $orderByString);
              continue;
            }
            $paramValue = ($param === "model") ? ($this->modelClassMap[$query[$operator]]) : ($query[$operator]);
            if ($operator === 'in') {
              $whereInParams[] = [$tableColumn, explode(',',   $paramValue)];
            } else {
              $whereParams[] = [$tableColumn, $this->operatorMap[$operator],   $paramValue];
            }
          }
        }
    
        return [
          "whereParams" =>   $whereParams,
          "whereInParams" =>  $whereInParams,
          "orderBy" => $orderBy,
          "orderDirection"=>$orderDirection
        ];
      }
}