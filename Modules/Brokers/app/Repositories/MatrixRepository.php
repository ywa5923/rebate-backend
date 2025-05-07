<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\MatrixDimension;
use Modules\Brokers\Transformers\MatrixDimensionCollection;

class MatrixRepository
{
    public function getMatrixColumnNames(int $matrixId, int $brokerId, int $zoneId, string $languageCode): array
    {
        $columns = MatrixDimension::with([
            'matrixHeader',
            'matrixHeader.translations' => function ($query) use ($languageCode) {
                $query->where('language_code', $languageCode);
            }
        ])->where(['type' => 'column', 'matrix_id' => $matrixId, 'broker_id' => $brokerId])
            ->where(function ($query) use ($zoneId) {
                $query->where('zone_id', $zoneId)
                    ->orWhere('is_invariant', 1);
            })->orderBy('order', 'asc')->get();

        $colsCollection = new MatrixDimensionCollection($columns);
        $resolvedColsCollection = $colsCollection->resolve();
        $columnTitles = [];

        foreach ($resolvedColsCollection as $col) {
            //$col['matrixHeader'] is a resource class
            //resolve the resource class to get the data
            //see definitions in Transformers/MatrixDimensionResource.php
            $matrixHeader = $col['matrixHeader']->resolve();
            $columnTitles[$matrixHeader['id']] = $matrixHeader['title'];
        }
        return $columnTitles;
    }

   

    public function getFormattedMatrix(int $matrixId,int $brokerId,int $zoneId,string $languageCode):array
    {
       
        $matrixCols=$this->getMatrixColumnNames($matrixId,$brokerId,$zoneId,$languageCode);
     
        $rows=$this->getFormatedRows($matrixId,$brokerId,$zoneId,$languageCode);
        $formatedMatrix=[array_values($matrixCols)];
        foreach($rows as $rowTitle=>$rowData){
            //the rowData is an array of the form:
            // "Ro Commission": [
            //     "1": "",
            //     "2": "$3 per side lot",
            //     "4": "",
            //     "3": "$3 per side lot"
            // ],
            $r=[$rowTitle];
            foreach($matrixCols as $colId=>$colTitle){
               // dd($colId);
                $r[]= $rowData[$colId] ?? null;
            }
            $formatedMatrix[]=$r;
        }
        return $formatedMatrix;
    }

   public function getFormatedRows(int $matrixId,int $brokerId,int $zoneId,string $languageCode):array
   {
  //  $matrixCols=$this->getMatrixColumnNames($matrixId,$brokerId,$zoneId,$languageCode);
   
    $rows=$this->getRowsData($matrixId,$brokerId,$zoneId,$languageCode);
    $rowsCollection=new MatrixDimensionCollection($rows);
    $resolvedRowsCollection=$rowsCollection->resolve();
    $formattedRows=[];
    foreach($rowsCollection->resolve() as $row){
        $matrixHeader = $row['matrixHeader']->resolve();
      $rowTitle = $matrixHeader['title'];
      
       $cells=$row['matrixRowCells']->resolve();
       $rowData=[];
       foreach($cells as $cell){
        $cellData=$cell['value'];
        $rowData[$cell['matrix_column_id']]=$cellData;
       }
       $formattedRows[$rowTitle]=$rowData;
    }
    return $formattedRows;
   }

    public function getRowsData(int $matrixId,int $brokerId,int $zoneId,string $languageCode)
    {
        $rows= MatrixDimension::with(['matrixHeader.translations'=>function($query) use ($languageCode){
            $query->where('language_code',$languageCode);
        },'matrixRowCells'=>function($query) use ($brokerId,$zoneId,$matrixId){
            $query->where(['broker_id'=>$brokerId,'matrix_id'=>$matrixId])
            ->where(function($query) use ($zoneId){
                $query->where('zone_id',$zoneId)
                ->orWhere('is_invariant',1);
            });
        }])->where(['type'=>'row','matrix_id'=>$matrixId,'broker_id'=>$brokerId])
        ->where(function($query) use ($zoneId){
            $query->where('zone_id',$zoneId)
            ->orWhere('is_invariant',1);
        })->orderBy('order','asc')->get();
        return $rows;
    }
}
        // $matrixRepository=new MatrixRepository();
        // $columnTitles=$matrixRepository->getMatrixColumnNames(1,1,1,'ro');
        
        // $rowsData=$matrixRepository->getRowsData(1,1,1,'ro');
    
        // $formattedMatrix=$matrixRepository->getFormattedMatrix(1,1,1,'ro');
        // return new JsonResponse([
        //     'status' => true,
        //     'data' =>   $formattedMatrix
        // ]);