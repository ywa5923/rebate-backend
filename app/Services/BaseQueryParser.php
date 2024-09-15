<?php

namespace App\Services;

use Illuminate\Http\Request;

class BaseQueryParser
{
    protected $querySafeParams = [
       
      ];
    
      protected $columnMap = [
      
      ];

      protected $whereParams = [];
      protected $whereInParams = [];
      protected $orderBy = [];
      protected $orderDirection = "";
    
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
    
      public function parse(Request $request)
      {
    
        //['column','operator','value']]
        
        //['column',['valuesArray']]
       
        
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
                $this->orderDirection = 'DESC';
                $orderByString=ltrim($orderByString,'-');
    
              } else {
                $this->orderDirection = 'ASC';
              }
    
              $this->orderBy= explode(',', $orderByString);
            
              continue;
            }
            $paramValue = ($param === "model") ? ($this->modelClassMap[$query[$operator]]) : ($query[$operator]);
            if ($operator === 'in') {
              $this->whereInParams[$param] = [$tableColumn, explode(',',   $paramValue)];
            } else {
              $this->whereParams[$param] = [$tableColumn, $this->operatorMap[$operator],   $paramValue];
            }
          }
        }

        return $this;
    
       
      }

      /**
       * Get the parsed query as an array
       *
       * @return array
       */

      public function getArrayResults()
      {
        return [
          "whereParams" =>    $this->whereParams,
          "whereInParams" =>   $this->whereInParams,
          "orderBy" =>  $this->orderBy,
          "orderDirection"=> $this->orderDirection
        ];
      }


      /**
       * Get the where params for given param
       *
       * @param string $param
       * @return array|null
       */

      public function getWhereParam($param):array|null
      {
        return $this->whereParams[$param]??null;
      }

      /**
       * Get the where in params
       *
       * @param string $param
       * @return array|null
       */

      public function getWhereInParam($param):array|null
      {
        return $this->whereInParams[$param]??null;
      }
      
      /**
       * Get the order by columns
       *
       * @return array
       */

      public function getOrderBy():array{
        return $this->orderBy;
      }


      /**
       * Get the order direction
       *
       * @return string
       */
      public function getOrderDirection():string{
        return $this->orderDirection;
      }



      /**
       * Get all where params from the parsed query
       *
       * @return array
       */

      public function getWhereParams():array{
        return $this->whereParams;
      }


      /**
       * Get all where in params from the parsed query
       *
       * @return array $whereInParams[$filterName]=[$column, $values]
       */

      public function getWhereInParams():array{
        return $this->whereInParams;
      }
      /**
       * Get all filters from the parsed query
       *
       * @return array $filters["whereIn"][$filterName]=$value, $filters["where"][$filterName]=$value
       */

      public function getAllFilters():array
      {
        $filters=[];
        foreach($this->whereInParams as $k=>$v)
        {
           if (str_contains($k,"filter_"))
           {
               $filters["whereIn"][$k]=$v;
           }
        }
        foreach($this->whereParams as $k=>$v)
        {
           if (str_contains($k,"filter_"))
           {
               $filters["where"][$k]=$v;
           }
        }

        return $filters;
      }
      
}