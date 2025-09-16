<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\MatrixHeaderRepository;
use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Repositories\MatrixDimensionRepository;
use Modules\Brokers\Repositories\MatrixRepository;

class MatrixService
{
    

    public function __construct(
        protected MatrixHeaderRepository $matrixHeaderRepository,
        protected MatrixDimensionRepository $matrixDimensionRepository,
        protected MatrixRepository $matrixRepository
    )
    {
        
    }

    
   /**
    * Refresh the matrix data
    * @param array $matrixData
    * @param int $brokerId
    * @param string $matrixName
    * @param int $matrixId
    * @param int|null $zoneId
    * @return void
    * @throws \Exception
    */
    public function saveMatrixData(array $matrixData, int $brokerId, string $matrixName, int $matrixId, ?int $zoneId = null): void
    {
        $this->matrixHeaderRepository->flushMatrix($matrixId, $brokerId,$zoneId);
        $headersSlugsWithOptions=$this->matrixHeaderRepository->insertHeadears($matrixData, $brokerId, $matrixName, $matrixId);
        $allHeaders = $this->matrixHeaderRepository->getAllHeaders(["name", "=", $matrixName], ['broker_id', '=', $brokerId], false);
        [$rowDimIds, $colDimIds]= $this->matrixHeaderRepository->insertMatrixDimensions($matrixData, $brokerId, $matrixName, $matrixId,$allHeaders);
        $this->matrixHeaderRepository->insertMatrixValues($matrixData, $brokerId, $matrixName, $matrixId,$rowDimIds,$colDimIds,$zoneId);
        $this->matrixHeaderRepository->insertDimensionOptions($headersSlugsWithOptions, $rowDimIds, $matrixId, $matrixName, $brokerId,$allHeaders);

    }

    public function compareSelectedRowHeaderSubOptions($previousSubOptions, $newSubOptions) {
        // Handle null/empty cases
        if (empty($previousSubOptions) && empty($newSubOptions)) {
            return true;
        }
        
        if (empty($previousSubOptions) || empty($newSubOptions)) {
            return false;
        }
        
        // Check if arrays have same length
        if (count($previousSubOptions) !== count($newSubOptions)) {
            return false;
        }
        
        // Sort both arrays by value for consistent comparison
        $sortedPrevious = $this->sortSubOptionsByValue($previousSubOptions);
        $sortedNew = $this->sortSubOptionsByValue($newSubOptions);
        
        // Compare each item
        for ($i = 0; $i < count($sortedPrevious); $i++) {
            if ($sortedPrevious[$i]['value'] !== $sortedNew[$i]['value'] || 
                $sortedPrevious[$i]['label'] !== $sortedNew[$i]['label']) {
                return false;
            }
        }
        
        return true;
    }
    
    private function sortSubOptionsByValue($subOptions) {
        // Create a copy to avoid modifying original array
        $sorted = $subOptions;
        
        // Sort by 'value' field
        usort($sorted, function($a, $b) {
            return strcmp($a['value'], $b['value']);
        });
        
        return $sorted;
    }
    public function getPrevCellValue($previousMatrixData, $rowSlug, $colSlug,$rowSubOptions){

       // $index=0;
         foreach($previousMatrixData as $row){
             foreach($row as $cell){
               //  $index==1 && dd($row[0]['selectedRowHeaderSubOptions'],$rowSubOptions);
                //  $index==1 && dd($this->compareSelectedRowHeaderSubOptions($row[0]['selectedRowHeaderSubOptions'],$rowSubOptions));
                 if($cell['rowHeader'] == $rowSlug 
                 && $cell['colHeader'] == $colSlug 
                 && $this->compareSelectedRowHeaderSubOptions($row[0]['selectedRowHeaderSubOptions'],$rowSubOptions)){
                     return $cell['value'];
                 }
             }
            // $index++;
         }
         return null;
    }

    public function setPreviousValueInMatrixData($previousMatrixData, &$newMatrixData){
       // $index=0;
        foreach($newMatrixData as $index => &$row){
           
            foreach($row as &$cell){
                $cellValueArray=$cell['value'];
                $rowSlug=$cell['rowHeader'];
                $colSlug=$cell['colHeader'];
                $rowSubOptions=$cell['selectedRowHeaderSubOptions'];
             
                $previousCellValueArray=$this->getPrevCellValue($previousMatrixData, $rowSlug, $colSlug,$rowSubOptions);
               //$index==1 && dd($previousCellValueArray, $cellValueArray);
               //  $index==1 && dd(empty(array_diff_assoc($previousCellValueArray, $cellValueArray)));
                if ($previousCellValueArray && !empty(array_diff_assoc($previousCellValueArray, $cellValueArray))){
                   $cell["previous_value"]=$previousCellValueArray;
                //  dd($cell);
                  //dd($previousCellValueArray, $cellValueArray);

                }
                
            }
           // $index++;
           
            
        }
        unset($row);
        return null;
    }

   public function getMatrixIdByName(string $matrixName): int
   {
    return $this->matrixRepository->getMatrixIdByName($matrixName);
   }

    public function getFormattedMatrix(string $matrixName, int $brokerId, ?int $zoneId): array
    {
        $matrixId=$this->getMatrixIdByName($matrixName);

        $dimensions=$this->matrixDimensionRepository->getMatrixDimensions($matrixId, $brokerId);
        $rowDimensions = $dimensions->where('type', 'row')->sortBy('order')->values();
        $columnDimensions = $dimensions->where('type', 'column')->sortBy('order')->values();

        $values = $this->matrixRepository->getMatrixValues($matrixId, $brokerId, $zoneId);

        // Create the matrix structure
        $matrixData = [];
        foreach ($rowDimensions as $rowIndex => $rowDim) {
            $row = [];
            foreach ($columnDimensions as $colIndex => $colDim) {
                $value = $values->first(function ($v) use ($rowDim, $colDim) {
                    return $v->matrix_row_id === $rowDim->id && $v->matrix_column_id === $colDim->id;
                });


                $cell = [
                    'previous_value' => $value ? json_decode($value->previous_value, true) : null,
                    'value' => $value ? json_decode($value->value, true) : null,
                    'public_value' => $value ? json_decode($value->public_value, true) : null,
                    'rowHeader' => $rowDim->matrixHeader->slug,
                    'colHeader' => $colDim->matrixHeader->slug,
                    'type' => $colDim->matrixHeader->formType->name ?? 'undefined'
                    //'selectedRowHeaderSubOptions' => $subOptions->toArray()
                ];


          //get the row headear options and add them to the cell in the first column
                if($colIndex == 0){
                    $options=$rowDim->matrixDimensionOptions;
                    $subOptions = $options->map(function ($option) {
                        return [
                            "value" => $option->optionSlug(),
                            "label" => $option->optionTitle()
                        ];
                    });
                    $cell['selectedRowHeaderSubOptions'] = $subOptions->toArray();
                }
                $row[] = $cell;
            }
            $matrixData[] = $row;
        }
        return $matrixData;
    }

}