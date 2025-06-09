<?php

namespace App\Utilities;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class BaseQueryParser
{
    protected $querySafeParams = [
       
      ];
    
      protected $validatorMap = [
       
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
          //$param=language
          //$query=["eq"=>"en"]
          
         
        
        
          if (!isset($query))
            continue;
    
          $tableColumn = $this->columnMap[$param] ?? $param;
    
          foreach ($operators as $operator) {
            if (!isset($query[$operator]))
              continue;
    
            //make validation
            if(isset($this->validatorMap[$param]))
            {
              $paramValue=$operator==="in" ? explode(',',$query[$operator]) : $query[$operator];
              $validator = Validator::make(['value' => $paramValue],
              ['value' => $this->validatorMap[$param]]);
              if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
              }
            }

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

            //remove mysql string operators from the begining of the param value. Ex lt1000=>1000
            $paramValue=$this->removeMysqlOperators($paramValue);

            if ($operator === 'in') {
              $this->whereInParams[$param] = [$tableColumn, explode(',',   $paramValue)];
            } else {
              $this->whereParams[$param] = [$tableColumn, $this->operatorMap[$operator],   $paramValue];
            }
          }
        }

        return $this;
    
       
      }

      public function removeMysqlOperators(string $paramValue):string
      {
        foreach($this->operatorMap as $k=>$v)
        {
          if(strpos($paramValue,$k)===0)
          {
            return preg_replace("/^{$k}/", "", $paramValue);
            
          }
        }

        return $paramValue;
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

      public function getWhereParam(string $param):array|null
      {
        return $this->whereParams[$param]??null;
      }


      /**
       * Extracts and deletes the where param from whereParams
       *
       * @param string $param
       * @return array|null
       */

      public function extractWhereParam(string $param):array|null
      {
        $extractedParam=$this->whereParams[$param]??null;
        unset($this->whereParams[$param]);
        return $extractedParam;
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