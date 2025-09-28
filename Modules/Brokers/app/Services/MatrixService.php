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
    public function saveMatrixData(array $matrixData, int $brokerId, string $matrixName, int $matrixId, ?int $zoneId = null, ?bool $isAdmin = null): void
    {
        $this->matrixHeaderRepository->flushMatrix($matrixId, $brokerId,$zoneId);
        $headersSlugsWithOptions=$this->matrixHeaderRepository->insertHeadears($matrixData, $brokerId, $matrixName, $matrixId);
        $allHeaders = $this->matrixHeaderRepository->getAllHeaders(["name", "=", $matrixName], ['broker_id', '=', $brokerId], false);
        [$rowDimIds, $colDimIds]= $this->matrixHeaderRepository->insertMatrixDimensions($matrixData, $brokerId, $matrixName, $matrixId,$allHeaders);
        $this->matrixHeaderRepository->insertMatrixValues($matrixData, $brokerId, $matrixName, $matrixId,$rowDimIds,$colDimIds,$zoneId,$isAdmin);
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

    /**
     * Get the previous cell value from the previous matrix data.
     * To get the previous value, we need to compare the row header slug, the column header slug and the selected row header sub options,
     *  because in the table can be multiple rows with the same row header slug, but different sub options.
     * @param array $previousMatrixData
     * @param string $rowSlug
     * @param string $colSlug
     * @param array $rowSubOptions
     * @return array|null
     */
    public function getPrevCellValue($previousMatrixData, $rowSlug, $colSlug,$rowSubOptions){

       // $index=0;
         foreach($previousMatrixData as $row){
             // Skip if row is not an array or is empty
             if (!is_array($row) || empty($row)) {
                 continue;
             }
             
             foreach($row as $cell){
                //get the previous row suboptions
                
              $previousRowSubOptions = $cell['selectedRowHeaderSubOptions'] ?? null;
                //the row subobtions are in the first cell of the row
               //  $index==1 && dd($row[0]['selectedRowHeaderSubOptions'],$rowSubOptions);
                //  $index==1 && dd($this->compareSelectedRowHeaderSubOptions($row[0]['selectedRowHeaderSubOptions'],$rowSubOptions));
                 if($cell['rowHeader'] == $rowSlug 
                 && $cell['colHeader'] == $colSlug 
               
                 && $this->compareSelectedRowHeaderSubOptions($previousRowSubOptions, $rowSubOptions)){
                     return $cell['value'];
                 }
             }
            // $index++;
         }
         return null;
    }

    /**
     * This is used to identify the updated entries and previous values in the matrix data.
     * Set the previous value in the matrix data.
     * If the cell value is different from the previous value found in the previous matrix data, set the previous value in the cell.
     * Also set the is_updated_entry to true.
     * @param array $previousMatrixData
     * @param array $newMatrixData
     * @return void
     */
    public function setPreviousValueInMatrixData($previousMatrixData, &$newMatrixData){
        $index=0;
        foreach($newMatrixData as $index => &$row){
           
            foreach($row as &$cell){
                $cellValueArray=$cell['value'];
                $rowSlug=$cell['rowHeader'];
                $colSlug=$cell['colHeader'];
                $rowSubOptions = $cell['selectedRowHeaderSubOptions'] ?? null;
             
                $previousCellValueArray=$this->getPrevCellValue($previousMatrixData, $rowSlug, $colSlug,$rowSubOptions);
                $index==2 && dd($previousCellValueArray);
                //$previousCellValueArray=json_decode($previousCellValue, true);
               // $index=2 && dd($previousCellValueArray,$cellValueArray,(array_diff_assoc($previousCellValueArray, $cellValueArray)));
                //$index==1 && dd($previousCellValueArray, $cellValueArray);
               //  $index==1 && dd(empty(array_diff_assoc($previousCellValueArray, $cellValueArray)));
                if ($previousCellValueArray && 
                !empty(array_diff_assoc($previousCellValueArray, $cellValueArray))){
                   $cell["previous_value"]=$previousCellValueArray;
                   $cell["is_updated_entry"]=true;
                //  dd($cell);
                  //dd($previousCellValueArray, $cellValueArray);

                }
                
            }
            $index++;
           
            
        }
        unset($row);
        return null;
    }

   public function getMatrixIdByName(string $matrixName): int
   {
    return $this->matrixRepository->getMatrixIdByName($matrixName);
   }

    /**
     * Get the formatted matrix data.
     * @param string $matrixName
     * @param int $brokerId
     * @param int|null $zoneId
     * @return array
     *
     * Example of a matrix with two rows and one column:
     * [
     *   [
     *     {
     *       "previous_value": null,
     *       "value": { "Number": "23" },
     *       "public_value": null,
     *       "is_updated_entry": 0,
     *       "rowHeader": "wwww",
     *       "colHeader": "zero-mt4",
     *       "type": "Number",
     *       "selectedRowHeaderSubOptions": [
     *         { "value": "q1-2", "label": "Q1" }
     *       ]
     *     }
     *   ],
     *   [
     *     {
     *       "previous_value": null,
     *       "value": { "Number": "12" },
     *       "public_value": null,
     *       "is_updated_entry": 0,
     *       "rowHeader": "wwww",
     *       "colHeader": "zero-mt4",
     *       "type": "Number",
     *       "selectedRowHeaderSubOptions": [
     *         { "value": "q1-2", "label": "Q1" },
     *         { "value": "q2", "label": "Q2" }
     *       ]
     *     }
     *   ]
     * ]
     */

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
                    'is_updated_entry' => $value ? $value->is_updated_entry : false,
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