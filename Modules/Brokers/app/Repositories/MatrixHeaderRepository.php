<?php


namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\MatrixHeader;
use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\MatrixDimension;
use Modules\Brokers\Models\MatrixValue;
use Modules\Brokers\Models\MatrixDimensionOption;

class MatrixHeaderRepository
{
    // /**
    //  * Get column headers for a matrix
    //  *
    //  * @param string $type The type of headers to retrieve
    //  * @param int $matrix_id The ID of the matrix
    //  * @param int|null $broker_id The ID of the broker (optional)
    //  * @param bool $broker_id_strict Whether to strictly match the broker ID.
    //  * If false,get headears where broker_id is null or the broker_id is the same as the broker_id in the request
    //  * @return Collection
    //  */
    // public function getColumnHeaders(?array $matrixNameCondition, ?array $brokerIdCondition, ?array $groupNameCondition, ?array $languageCode, ?bool $broker_id_strict = false): Collection
    // {
    //     //$matrixNameCondition is an array of 3 elements:
    //     // array:3 [ 
    //     //     0 => "name"
    //     //     1 => "="
    //     //     2 => "Matrix-1"
    //     //   ]
    //     // $brokerIdCondition is an array of 3 elements:
    //     // array:3 [ 
    //     //     0 => "broker_id"
    //     //     1 => "="
    //     //     2 => "1"
    //     //   ]
    //     // $broker_id_strict is a boolean value
    //     // $groupNameCondition is an array of 3 elements:
    //     // array:3 [ 
    //     //     0 => "group_name"
    //     //     1 => "="
    //     //     2 => "Group 1"-
    //     //   ]

    //     $languageCode = $languageCode ?? ['language_code','=','en'];
    //     $withArray = ['formType.items.dropdown.dropdownOptions'];
    //     if($languageCode[2]!='en'){
    //         $withArray['translations']= function($query) use ($languageCode) {
    //             $query->where(...$languageCode); // or any specific language
    //         };
    //     }

    //     if(is_array($groupNameCondition)){
           
    //         return MatrixHeader::with($withArray)
    //         ->where(...$groupNameCondition)
    //         ->where('type', 'column')
    //         ->get();
    //     }else{
    //     return MatrixHeader::with($withArray)
    //         ->where(function ($query) use ($brokerIdCondition, $broker_id_strict) {
    //             if ($broker_id_strict && !empty($brokerIdCondition)) {
    //                 $query->where(...$brokerIdCondition);
    //             } else {
    //                 $query->whereNull('broker_id');
    //                 if ($brokerIdCondition)
    //                     $query->orWhere(...$brokerIdCondition);
    //             }
    //         })
    //         ->where('type', 'column')
    //         //->where(...$matrixIdCondition)
    //         ->whereHas('matrix', function ($query) use ($matrixNameCondition) {
    //             $query->where(...$matrixNameCondition);
    //         })
    //         ->get();
    //     }
    // }

    /**
     * Get column or row headers for a matrix by type
     *
     * @param string $type The type of headers to retrieve
     * @param array|null $matrixNameCondition The condition for the matrix name
     * @param array|null $brokerIdCondition The condition for the broker ID
     * @param array|null $groupNameCondition The condition for the group name
     * @param array|null $languageCode The condition for the language code
     * @param bool $broker_id_strict Whether to strictly match the broker ID.
     * If false,get headears where broker_id is null or the broker_id is the same as the broker_id in the request
     * @param bool $withChildren Whether to include the children of the row headers
     * @return Collection
     */
    public function getColumnHeadearsByType(string $type, ?array $matrixNameCondition, ?array $brokerIdCondition, ?array $groupNameCondition, ?array $languageCode, ?bool $broker_id_strict = false): Collection
    {
         //$matrixNameCondition is an array of 3 elements:
        // array:3 [ 
        //     0 => "name"
        //     1 => "="
        //     2 => "Matrix-1"
        //   ]
        // $brokerIdCondition is an array of 3 elements:
        // array:3 [ 
        //     0 => "broker_id"
        //     1 => "="
        //     2 => "1"
        //   ]
        // $broker_id_strict is a boolean value
        // $groupNameCondition is an array of 3 elements:
        // array:3 [ 
        //     0 => "group_name"
        //     1 => "="
        //     2 => "Group 1"-
        //   ]
        $languageCode = $languageCode ?? ['language_code','=','en'];
        //$withArray = ['formType.items.dropdown.dropdownOptions'];
        $withArray = [
            'formType.items' => function($query) {
                $query->orderBy('form_type_form_item.id','asc');
            },
            'formType.items.dropdown.dropdownOptions'
        ];
        if($languageCode[2]!='en'){
            $withArray['translations']= function($query) use ($languageCode) {
                $query->where(...$languageCode); // or any specific language
            };
        }

        if(is_array($groupNameCondition)){
           
            return MatrixHeader::with($withArray)
            ->where(...$groupNameCondition)
            ->where('type', $type)
            ->get();
        }else{
        $qb= MatrixHeader::with($withArray)
            ->where(function ($query) use ($brokerIdCondition, $broker_id_strict) {
                if ($broker_id_strict && !empty($brokerIdCondition)) {
                    $query->where(...$brokerIdCondition);
                } else {
                    $query->whereNull('broker_id');
                    if ($brokerIdCondition)
                        $query->orWhere(...$brokerIdCondition);
                }
            })
            ->where('type', $type);

            if($type=='row'){
                $qb->whereNull('parent_id')->with('children');
            }

            //->where(...$matrixIdCondition)
           return  $qb->whereHas('matrix', function ($query) use ($matrixNameCondition) {
                $query->where(...$matrixNameCondition);
            })
            ->get();
        }
        
    }

    // public function getRowHeaders(array $matrixNameCondition, ?array $brokerIdCondition, $broker_id_strict = false): Collection
    // {
    //     return MatrixHeader::where(function ($query) use ($brokerIdCondition, $broker_id_strict) {
    //         if ($broker_id_strict &&  !empty($brokerIdCondition)) {
    //             $query->where(...$brokerIdCondition);
    //         } else {
    //             $query->whereNull('broker_id');
    //             if ($brokerIdCondition)
    //                 $query->orWhere(...$brokerIdCondition);
    //         }
    //     })
    //         ->where('type', 'row')
    //         ->whereNull('parent_id')
    //         ->whereHas('matrix', function ($query) use ($matrixNameCondition) {
    //             $query->where(...$matrixNameCondition);
    //         })
    //         ->with('children')
    //         ->get();
    // }

    public function getAllHeaders(array $matrixNameCondition, ?array $brokerIdCondition, $broker_id_strict = false): Collection
    {
        return MatrixHeader::where(function ($query) use ($brokerIdCondition, $broker_id_strict) {
            if ($broker_id_strict &&  !empty($brokerIdCondition)) {
                $query->where(...$brokerIdCondition);
            } else {
                $query->whereNull('broker_id');
                if ($brokerIdCondition)
                    $query->orWhere(...$brokerIdCondition);
            }
        })
            //->whereNull('parent_id')
            ->whereHas('matrix', function ($query) use ($matrixNameCondition) {
                $query->where(...$matrixNameCondition);
            })
            ->get();
    }
    public function flushMatrix(int $matrixId,int $brokerId,?int $zoneId=null)
    {
        MatrixDimensionOption::where(['matrix_id'=>$matrixId,'broker_id'=>$brokerId])->delete();
        MatrixDimension::where(['matrix_id'=>$matrixId,'broker_id'=>$brokerId])->delete();
        MatrixHeader::where(['matrix_id'=>$matrixId,'broker_id'=>$brokerId,'type'=>'row'])->delete();
        if($zoneId){
            MatrixValue::where(['matrix_id'=>$matrixId,'broker_id'=>$brokerId,'zone_id'=>$zoneId])->delete();
        }else{
            MatrixValue::where(['matrix_id'=>$matrixId,'broker_id'=>$brokerId])->delete();
        }
       
    }
    public function insertSelectedSubOptions(array $headearSelectedSubOptions, Collection $allHeaders,int $matrixId,int $brokerId)
    {
        $headerOptions = [];
        foreach ($headearSelectedSubOptions as $rowHeaderSlug => $subOptionSlugs) {
            $rowHeaderId = $this->getHeaderId($rowHeaderSlug, $allHeaders);
            foreach ($subOptionSlugs as $subOptionSlug) {
                $subOptionId = $this->getHeaderId($subOptionSlug, $allHeaders);
                if ($subOptionId != null) {
                    // $rowHeaderSubOptionsIds[$rowHeaderId][] = $subOptionId;
                    $headerOptions[] = [
                        'matrix_id' => $matrixId,
                        'broker_id' => $brokerId,
                        'matrix_header_id' => $rowHeaderId,
                        'sub_option_id' => $subOptionId,
                    ];
                }
            }
        }
        MatrixDimensionOption::insert($headerOptions);
    }

    public function insertDimensionOptions(array $headearsSelectedOptions, array $rowDimIds, int $matrixId, string $matrixName, int $brokerId,Collection $allHeaders)
    {
      //  $allHeaders = $this->getAllHeaders(["name", "=", $matrixName], ['broker_id', '=', $brokerId], false);
        $dimensionOptions = [];
        foreach ($headearsSelectedOptions as $rowIndex => $optionsSlugs) {
            //$rowIndex is the index of the row in the matrix
            //$rowDimIds[$rowIndex] is the id of the row dimension
            foreach ($optionsSlugs as $optionSlug) {
                $optionId = $this->getHeaderId($optionSlug, $allHeaders);
                $dimensionOptions[] = [
                    'matrix_id' => $matrixId,
                    'broker_id' => $brokerId,
                    'matrix_dimension_id' => $rowDimIds[$rowIndex],
                    'option_id' => $optionId,
                ];
            }
        }
        MatrixDimensionOption::insert($dimensionOptions);
    }

    public function getHeaderId($headerSlug, $headers)
    {
        $header = $headers->firstWhere('slug', $headerSlug);
        return $header ? $header->id : null;
    }


    //Example of a matrix row
            //First cell in a row contains the selectedRowHeaderSubOptions        
            // [
            //     {
            //         "value": {
            //             "Number": "hyrt"
            //         },
            //         "public_value": null,
            //         "rowHeader": "wwwwww",
            //         "colHeader": "zero-mt4",
            //         "type": "Number",
            //         "selectedRowHeaderSubOptions": [
            //             {
            //                 "value": "qqqqqq-2-2",
            //                 "label": "Qqqqqq"
            //             }
            //         ]
            //     },
            //     {
            //         "value": {
            //             "Number": "hrherth"
            //         },
            //         "public_value": null,
            //         "rowHeader": "wwwwww",
            //         "colHeader": "zero-mt4",
            //         "type": "Number"
            //     }
            // ],
    public function insertHeadears(array $matrixData,int $brokerId,string $matrixName,int $matrixId)
    {
        $headearsSelectedSubOptions = [];
        //GET original row HEADERS FOR THE MATRIX where broker_id is null
        $allHeaders = $this->getAllHeaders(["name", "=", $matrixName], ['broker_id','=', $brokerId], false);
        $newHeaders = [];
        $newHeadersSubOptions = [];
       // $headearsSelectedSubOptions = [];
        $usedSlugs = [];
        // Create all headers first

        
        foreach ($matrixData as $rowIndex => $row) {
            $selectedRowHeaderSubOptions = $row[0]['selectedRowHeaderSubOptions'] ?? null;
            $rowHeaderSlug = $row[0]['rowHeader'];
            $usedSlugs[] = $rowHeaderSlug;
            //Get the id of the main headear (class instrument),which has parent_id=null
            //Sub headears (instruments) are stored in matrix_headears with parent_id=rowHeaderId

            //1.first scan matrix for new main headears (parent_id=null) which doesn't exist in matrix_headears,
            //and save them in newHeaders for later save in matrix_headears with broker_id using batch insert

            //get the id of the main headear (class instrument),which has parent_id=null
            $rowHeaderId = $this->findHeaderIdBySlugAndParent($rowHeaderSlug, $allHeaders,true);
            $headearExists = in_array($rowHeaderSlug, array_column($newHeaders, 'slug'));
          
            if ($rowHeaderId == null && !$headearExists){
                $title=ucwords(str_replace('-', ' ', $rowHeaderSlug));

                $newHeaders[] = [
                    'title' => $title,
                    'slug' =>   $rowHeaderSlug,
                    'broker_id' => $brokerId,
                    'type' => 'row',
                    'matrix_id' => $matrixId
                ];
            }
            $updatedRowSlugs[]=$rowHeaderSlug;
                
           //2.Scan matrix for new sub headears (instruments) which doesn't exist in matrix_headears,
           //and save them in newHeadersSubOptions for later save in matrix_headears using batch insert
         
            if ($selectedRowHeaderSubOptions) {
                foreach ($selectedRowHeaderSubOptions as $subOption) {
                //get the id of the sub headear (instrument),which has parent_id=rowHeaderId
                //if the sub option doesn't exist in the allHeaders array, it will return null
                $subOptionId = $this->findHeaderIdBySlugAndParent($subOption['value'], $allHeaders,false);
               
                $slug=$subOption['value'];
                $subOptionTitle = ucwords(str_replace('-', ' ',    $subOption['label']));
                $subOptionExists = in_array( $subOptionTitle, array_column($newHeadersSubOptions[$rowHeaderSlug] ?? [], 'title'));
          
                if ($subOptionId == null && !$subOptionExists) {
                   
                    $slug = $this->getUniqueSlug($slug, $usedSlugs);
                    $newHeadersSubOptions[$rowHeaderSlug][]=
                        [
                            'parent_id' => $rowHeaderId,//$rowHeaderId is null for new row headears, it will be set later after save new row headears
                            'slug' =>  $slug,
                            'title' => $subOptionTitle,
                            'broker_id' => $brokerId,
                            'type' => 'row',
                            'matrix_id' => $matrixId
                        ];
                }
                $usedSlugs[] = $slug;
                $headearsSelectedSubOptions[$rowIndex][] = $slug;
                }
            }
        
      
        } //end of matrix foreach
       
        //save new main headears
        //this allow to get their ids for later save sub_headears in matrix_headears with parent_id=main_headear_id
       if (!empty($newHeaders)) {
           MatrixHeader::insert($newHeaders);
       }
       // dd( $headearsSelectedSubOptions);
       $brokerHeadears = $this->getAllHeaders(["name", "=", $matrixName], ['broker_id', '=', $brokerId], false);
    
        foreach ($newHeadersSubOptions as $rowHeaderSlug => &$subOptionArray) {
            $rowHeaderId = $this->findHeaderIdBySlugAndParent($rowHeaderSlug, $brokerHeadears, true);
            foreach ($subOptionArray as &$subOption) {
               
                $subOption['parent_id'] = $rowHeaderId;
            }
            unset($subOption);
        }
       
        unset($subOptionArray); 
        
        //save new sub headears
        MatrixHeader::insert(array_merge(...array_values($newHeadersSubOptions)));

        return $headearsSelectedSubOptions;
    }

    public function insertMatrixDimensions(array $matrixData,int $brokerId,string $matrixName,int $matrixId,Collection $allHeaders)
    {
        $rowDimensions = [];
        $columnDimensions = [];
        
        foreach ($matrixData as $rowIndex => $row) {
            $rowHeaderSlug = $row[0]['rowHeader'];
           // $updatedRowSlug = $updatedRowSlugs[$rowIndex];
            $rowHeaderId = $this->findHeaderIdBySlugAndParent( $rowHeaderSlug, $allHeaders,true);

            // Add row dimension
            $rowDimensions[] = [
                'matrix_id' => $matrixId,
                'broker_id' => $brokerId,
                'order' => $rowIndex,
                'matrix_header_id' => $rowHeaderId,
                'type' => 'row'
            ];

            foreach ($row as $cellIndex => $cell) {
                $colHeaderSlug = $cell['colHeader'];
                $colHeaderId = $this->findHeaderIdBySlugAndParent($colHeaderSlug, $allHeaders,true);

                if (!isset($columnDimensions[$cellIndex])) {
                    $columnDimensions[$cellIndex] = [
                        'matrix_id' => $matrixId,
                        'broker_id' => $brokerId,
                        'order' => $cellIndex,
                        'matrix_header_id' => $colHeaderId,
                        'type' => 'column'
                    ];
                }

                if ($colHeaderId == null) {
                    throw new \Exception("Column header not found");
                }

               
            }
        }

        // Bulk insert dimensions
        MatrixDimension::insert($rowDimensions);
        MatrixDimension::insert(array_values($columnDimensions));
          // Get the inserted IDs
          $rowDimIds = MatrixDimension::where('matrix_id', $matrixId)
          ->where('type', 'row')
          ->where('broker_id', $brokerId)
          ->orderBy('order')
          ->pluck('id')
          ->toArray();
  
      $colDimIds = MatrixDimension::where('matrix_id', $matrixId)
          ->where('type', 'column')
          ->where('broker_id', $brokerId)
          ->orderBy('order')
          ->pluck('id')
          ->toArray();

          return [$rowDimIds, $colDimIds];


    }

    public function insertMatrixValues(array $matrixData,int $brokerId,string $matrixName,int $matrixId,$rowDimIds,$colDimIds,?int $zoneId=null, ?bool $isAdmin = null)
    {
      

        $matrixValues = [];
        foreach ($matrixData as $rowIndex => $row) {
            foreach ($row as $cellIndex => $cell) {
                $matrixValues[] = [
                    'matrix_id' => $matrixId,
                    'broker_id' => $brokerId,
                    'matrix_row_id' => $rowDimIds[$rowIndex],
                    'matrix_column_id' => $colDimIds[$cellIndex],
                    'value' => json_encode($cell['value']),
                    'public_value' =>$cell['public_value'] ? json_encode($cell['public_value']) : null,
                    'previous_value' => isset($cell['previous_value']) ? json_encode($cell['previous_value']) : null,
                    //deprecated: if isAdmin is true, set is_updated_entry is set in frontend correctly
                   // 'is_updated_entry' => $isAdmin ? 0 :$cell['is_updated_entry'] ?? false,
                    'is_updated_entry' => $cell['is_updated_entry'] ?? false,
                    'zone_id' => $zoneId
                ];
            }
        }
            // Bulk insert values
            MatrixValue::insert($matrixValues);
    }


    public function getUniqueSlug($slug,$usedSlugs){
      
        if(in_array($slug, $usedSlugs)){
            $slug = $slug. '-' . count($usedSlugs);
           
        }
        return $slug;
    }

    public function findHeaderIdBySlugAndParent($headerSlug, $headers, $onlyParent = false): int|null
    {
        $header = null;
        if($onlyParent){
            $header = $headers->firstWhere(function($header) use ($headerSlug) {
                return $header->slug === $headerSlug && $header->parent_id === null;
            });
        }else{
            $header = $headers->firstWhere(function($header) use ($headerSlug) {
                return $header->slug === $headerSlug && $header->parent_id !== null;
            });
        }

        return $header ? $header->id : null;
        
    }

    public function getColumnMatrixHeaderByTitle(string $title,$broker_id): MatrixHeader|null
    {
        return MatrixHeader::where('title', $title)->where('type', 'column')->where('broker_id', $broker_id)->first();
    }

    public function create(array $data): MatrixHeader
    {
        return MatrixHeader::create($data);
    }

    public function deleteById(int $id): bool
    {
        return MatrixHeader::where('id', $id)->delete();
    }

}
