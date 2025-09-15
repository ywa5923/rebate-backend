<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Brokers\Models\MatrixHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Brokers\Transformers\MatrixHeaderResource;
use Modules\Brokers\Services\MatrixHeadearsQueryParser;
use Modules\Brokers\Repositories\MatrixHeaderRepository;
use Modules\Brokers\Models\Matrix;
use Modules\Brokers\Models\MatrixDimension;
use Modules\Brokers\Models\MatrixValue;
use Modules\Brokers\Models\MatrixHeaderOption;
use Illuminate\Support\Str;

class MatrixController_old extends Controller
{
    public function getHeaders(MatrixHeadearsQueryParser $queryParser, Request $request, MatrixHeaderRepository $rep)
    {
        $queryParser->parse($request);
     // dd( $queryParser->getWhereParams());

        if (empty($queryParser->getWhereParams())) {
            return new Response("not found", 404);
        }

        $columnHeaders = $rep->getColumnHeadearsByType(
            'column',
            $queryParser->getWhereParam("matrix_id") ?? null,
            $queryParser->getWhereParam("broker_id") ?? null,
            $queryParser->getWhereParam("col_group") ?? null,
            $queryParser->getWhereParam("language") ?? null,
            $queryParser->getWhereParam("broker_id_strict")[2] ?? false
        );

        //return $columnHeaders;

        $rowHeaders = $rep->getColumnHeadearsByType(
            'row',
            $queryParser->getWhereParam("matrix_id") ?? null,
            $queryParser->getWhereParam("broker_id") ?? null,
            $queryParser->getWhereParam("row_group") ?? null,
            $queryParser->getWhereParam("language") ?? null,
            $queryParser->getWhereParam("broker_id_strict")[2] ?? false
        );

      
        return [
            'columnHeaders' => MatrixHeaderResource::collection($columnHeaders),
            'rowHeaders' => MatrixHeaderResource::collection($rowHeaders)
        ];
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

       $index=0;
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
            $index++;
        }
        return null;
    }

    public function compareMatrixData($previousMatrixData, &$newMatrixData){
        $index=0;
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
            $index++;
           
            
        }
        unset($row);
        return null;
    }

    public function store(Request $request, MatrixHeaderRepository $rep)
    {
        $startTime = microtime(true);

        $previousMatrixResponse = $this->index($request);
        $previousMatrixData = json_decode($previousMatrixResponse->getContent(), true);
        
       
        
       

        $data = $request->validate([
            'matrix' => 'array',
            'broker_id' => 'required|integer',
            'matrix_id' => 'required|string',
        ]);

        $matrixName = $data['matrix_id'];
        $brokerId = $data['broker_id'];
        $matrix = Matrix::where("name", "=", $matrixName)->first();

       // dd($previousMatrixData['matrix']);
        if($previousMatrixData['matrix']){
            $this->compareMatrixData($previousMatrixData['matrix'], $data['matrix']);
        }
      //  dd($data['matrix']);
            
        $updatedRowSlugs=[];
        try {
            // First transaction - Flush data
            DB::beginTransaction();
            //flush all data for the matrix and headers and headears options
            $rep->flushMatrix($matrix->id, $brokerId);
            DB::commit();

            if (empty($data['matrix'])) {
                return response()->json([
                    'message' => 'Matrix data was deleted successfully',
                ]);
            }

            // Second transaction - Create headers
            // DB::beginTransaction();
            $allHeaders = $rep->getAllHeaders(["name", "=", $matrixName],['broker_id','=', $brokerId], false);

            $newHeaders = [];
            $newHeadersSubOptions = [];
            $headearsSelectedSubOptions = [];
            $usedSlugs = [];
          
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
           
            // Create all headers first
            foreach ($data['matrix'] as $rowIndex => $row) {
                $selectedRowHeaderSubOptions = $row[0]['selectedRowHeaderSubOptions'] ?? null;
                $rowHeaderSlug = $row[0]['rowHeader'];
               
                //Get the id of the main headear (class instrument),which has parent_id=null
                //Sub headears (instruments) are stored in matrix_headears with parent_id=rowHeaderId

                //1.first scan matrix for new main headears (parent_id=null) which doesn't exist in matrix_headears,
                //and save them in newHeaders for later save in matrix_headears with broker_id using batch insert
                $rowHeaderId = $this->getHeaderId($rowHeaderSlug, $allHeaders,true);
                // Remove the unique suffix added by getUniqueSlug function (e.g., "-2", "-3")
                $originalSlug = preg_replace('/-\d+$/', '', $rowHeaderSlug);
                $title = ucwords(str_replace('-', ' ', $originalSlug));
                $headearExists = in_array($title, array_column($newHeaders, 'title'));
               // $slug=$rowHeaderSlug;
                // $slug=$headearExists ? $this->getUniqueSlug($rowHeaderSlug, $usedSlugs) : $rowHeaderSlug;
                //if ($rowHeaderId == null && !$headearExists){
                    if ($rowHeaderId == null && !$headearExists){
                    //$slug=$headearExists ? $this->getUniqueSlug($rowHeaderSlug, $usedSlugs) : $rowHeaderSlug;
                  // $rowHeaderSlug = $this->getUniqueSlug($rowHeaderSlug, $usedSlugs);
                   
                    $newHeaders[] = [
                        'title' => $title,
                        'slug' =>   $rowHeaderSlug,
                        'broker_id' => $brokerId,
                        'type' => 'row',
                        'matrix_id' => $matrix->id
                    ];
                }
                $usedSlugs[] = $rowHeaderSlug;
                $updatedRowSlugs[]=$rowHeaderSlug;
                //$updatedRowSlugs[]=$slug;
                    
               //2.Scan matrix for new sub headears (instruments) which doesn't exist in matrix_headears,
               //and save them in newHeadersSubOptions for later save in matrix_headears using batch insert
               
               //added new check for selectedRowHeaderSubOptions
                if ($selectedRowHeaderSubOptions) {
                    foreach ($selectedRowHeaderSubOptions as $subOption) {
                    //get the id of the sub headear (instrument),which has parent_id=rowHeaderId
                    //if the sub option doesn't exist in the allHeaders array, it will return null
                
                    $subOptionId = $this->getHeaderId($subOption['value'], $allHeaders,false);
                    $slug = $subOption['value'];
                   // $usedSlugs[] = $subOption['value'];
                   
                   //$slug=$subOption['value'];
                
                    $subOptionTitle = ucwords(str_replace('-', ' ',    $subOption['label']));
                    //check if the sub option already exists in the newHeadersSubOptions array
                    // $subOptionExists = in_array($subOptionTitle, array_column($newHeadersSubOptions[$rowHeaderSlug], 'title'));
                     $subOptionExists = in_array($subOptionTitle, array_column($newHeadersSubOptions[$rowHeaderSlug] ?? [], 'title'));
                    //  if($subOptionExists){
                    //     dd( $subOptionTitle,$slug);
                    //  }
                    
                    // if ($subOptionId == null && !$subOptionExists) {
                        if ($subOptionId == null && !$subOptionExists) {
                        //$usedSlugs[] = $subOption['value'];

                        $slug = $this->getUniqueSlug($slug, $usedSlugs);
                        
                      
                        $newHeadersSubOptions[$rowHeaderSlug][]=
                            [
                                'parent_id' => $rowHeaderId,//$rowHeaderId is null for new row headears, it will be set later after save new row headears
                                'slug' =>  $slug,
                                'title' => $subOptionTitle,
                                'broker_id' => $brokerId,
                                'type' => 'row',
                                'matrix_id' => $matrix->id
                            ];
                    }
                    $usedSlugs[] = $slug;
                  
                    //$headearSelectedSubOptions[$rowHeaderSlug][] = $subOption['value'];
                   // $headearsSelectedSubOptions[$rowIndex][] = $subOption['value'];
                    $headearsSelectedSubOptions[$rowIndex][] = $slug;
                    }
                }
             
              
                   
            } //end of matrix foreach
          
           
          
            //save new main headears
            //this allow to get their ids for later save sub_headears in matrix_headears with parent_id=main_headear_id
           !empty($newHeaders) && MatrixHeader::insert($newHeaders);
           // dd( $headearsSelectedSubOptions);
           $brokerHeadears = $rep->getAllHeaders(["name", "=", $matrixName], ['broker_id', '=', $brokerId], false);
        
            foreach ($newHeadersSubOptions as $rowHeaderSlug => &$subOptionArray) {
                $rowHeaderId = $this->getHeaderId($rowHeaderSlug, $brokerHeadears,true);
                foreach ($subOptionArray as &$subOption) {
                   
                    $subOption['parent_id'] = $rowHeaderId;
                }
                unset($subOption);
            }
           
            unset($subOptionArray); 
           // dd($newHeadersSubOptions);
            //save new sub headears
            MatrixHeader::insert(array_merge(...array_values($newHeadersSubOptions)));


            //grab again all headears
            $allHeaders = $rep->getAllHeaders(["name", "=", $matrixName], ['broker_id', '=', $brokerId], false);
           
            //$rep->insertSelectedSubOptions($headearSelectedSubOptions, $allHeaders, $matrix->id, $brokerId);

        } catch (\Exception $e) {
            // Log::error('Matrix store error: ' . $e->getMessage());
           
            DB::beginTransaction();
            try {
                MatrixHeader::where('matrix_id', $matrix->id)
                    ->where('broker_id', $brokerId)
                    ->delete();
                DB::commit();
            } catch (\Exception $deleteError) {
                DB::rollBack();
                Log::error('Failed to rollback headers: ' . $deleteError->getMessage());
            }

            return response()->json([
                'message' => 'Failed to save matrix data2',
                'error' => $e->getMessage()
            ], 500);
        
        }

        // Third transaction - Create dimensions, values and options
        DB::beginTransaction();
        try {
            // Prepare bulk insert data
            $rowDimensions = [];
            $columnDimensions = [];
            $matrixValues = [];

            foreach ($data['matrix'] as $rowIndex => $row) {
                $rowHeaderSlug = $row[0]['rowHeader'];
                $updatedRowSlug = $updatedRowSlugs[$rowIndex];
                $rowHeaderId = $this->getHeaderId($updatedRowSlug, $allHeaders,true);


                // Add row dimension
                $rowDimensions[] = [
                    'matrix_id' => $matrix->id,
                    'broker_id' => $brokerId,
                    'order' => $rowIndex,
                    'matrix_header_id' => $rowHeaderId,
                    'type' => 'row'
                ];

                foreach ($row as $cellIndex => $cell) {
                    $colHeaderSlug = $cell['colHeader'];
                    $colHeaderId = $this->getHeaderId($colHeaderSlug, $allHeaders,true);

                    if (!isset($columnDimensions[$cellIndex])) {
                        $columnDimensions[$cellIndex] = [
                            'matrix_id' => $matrix->id,
                            'broker_id' => $brokerId,
                            'order' => $cellIndex,
                            'matrix_header_id' => $colHeaderId,
                            'type' => 'column'
                        ];
                    }

                    if ($colHeaderId == null) {
                        throw new \Exception("Column header not found");
                    }

                    $matrixValues[] = [
                        'matrix_id' => $matrix->id,
                        'broker_id' => $brokerId,
                        'row_index' => $rowIndex,
                        'col_index' => $cellIndex,
                        'value' => json_encode($cell['value']),
                        'public_value' =>$cell['public_value'] ? json_encode($cell['public_value']) : null,
                        'previous_value' => isset($cell['previous_value']) ? json_encode($cell['previous_value']) : null
                    ];
                }
            }

            // Bulk insert dimensions
            MatrixDimension::insert($rowDimensions);
            MatrixDimension::insert(array_values($columnDimensions));

            // Get the inserted IDs
            $rowDimIds = MatrixDimension::where('matrix_id', $matrix->id)
                ->where('type', 'row')
                ->where('broker_id', $brokerId)
                ->orderBy('order')
                ->pluck('id')
                ->toArray();

            $colDimIds = MatrixDimension::where('matrix_id', $matrix->id)
                ->where('type', 'column')
                ->where('broker_id', $brokerId)
                ->orderBy('order')
                ->pluck('id')
                ->toArray();

            // Update matrix values with correct dimension IDs
            foreach ($matrixValues as &$value) {
                $value['matrix_row_id'] = $rowDimIds[$value['row_index']];
                $value['matrix_column_id'] = $colDimIds[$value['col_index']];
                unset($value['row_index'], $value['col_index']);
            }

            //dd($rowDimIds, $colDimIds);
            // Bulk insert values
            MatrixValue::insert($matrixValues);
           
            // Bulk insert dimension options
            $rep->insertDimensionOptions($headearsSelectedSubOptions, $rowDimIds, $matrix->id, $brokerId,   $allHeaders);
 

            // // Create header options (now safe because headers exist)
            // foreach ($rowHeaderSubOptionsIds as $rowHeaderId => $subOptionIds) {
            //     $data = [];
            //     foreach ($subOptionIds as $subOptionId) {
            //         $data[] = [
            //             'matrix_id' => $matrix->id,
            //             'broker_id' => $brokerId,
            //             'matrix_header_id' => $rowHeaderId,
            //             'sub_option_id' => $subOptionId,
            //         ];
            //     }
            //     MatrixHeaderOption::insert($data);
            // }

            DB::commit();

            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;

            return response()->json([
                'message' => 'Matrix data saved successfully',
                'performance' => [
                    'execution_time_ms' => $executionTime,
                    'rows_count' => count($rowDimensions),
                    'columns_count' => count($columnDimensions),
                    'values_count' => count($matrixValues)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
           
                // If headers were created and third transaction failed,
                // we need to delete the headers we just created
                DB::beginTransaction();
                try {
                    MatrixHeader::where('matrix_id', $matrix->id)
                        ->where('broker_id', $brokerId)
                        ->delete();
                    DB::commit();
                } catch (\Exception $deleteError) {
                    DB::rollBack();
                    Log::error('Failed to rollback headers: ' . $deleteError->getMessage());
                }
            
            throw $e;
        }
    }

    public function getHeaderId($headerSlug, $headers,$onlyParent=false):int|null
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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // $matrixId = "Matrix-1";
        // $brokerId = 181;
        // $oldMAtrix=  MatrixValue::where('matrix_id', $matrixId)
        // ->where('broker_id', $brokerId)
        // ->get();

        // dd($oldMAtrix);

        $matrixId = $request->query('matrix_id');
        $brokerId = $request->query('broker_id');
        $is_admin = $request->query('is_admin');
        $matrixId = "Matrix-1";
        $brokerId = 181;
        $is_admin = true;
        if (!$matrixId || !$brokerId) {
            return response()->json(['error' => 'matrix_id and broker_id are required'], 400);
        }

        $matrix = Matrix::where('name', $matrixId)->first();
        if (!$matrix) {
            return response()->json(['error' => 'Matrix not found'], 404);
        }

        // Get all dimensions for this matrix and broker
        $dimensions = MatrixDimension::where('matrix_id', $matrix->id)
            ->where('broker_id', $brokerId)
            ->with(['matrixHeader', 'matrixDimensionOptions', 'matrixDimensionOptions.option'])
            ->get();


        // Separate row and column dimensions
        $rowDimensions = $dimensions->where('type', 'row')->sortBy('order')->values();
        $columnDimensions = $dimensions->where('type', 'column')->sortBy('order')->values();


        // Get all values for this matrix and broker
        $values = MatrixValue::where('matrix_id', $matrix->id)
            ->where('broker_id', $brokerId)
            ->get();

        // Create the matrix structure
        $matrixData = [];
        foreach ($rowDimensions as $rowIndex => $rowDim) {
            $row = [];
            foreach ($columnDimensions as $colIndex => $colDim) {
                $value = $values->first(function ($v) use ($rowDim, $colDim) {
                    return $v->matrix_row_id === $rowDim->id && $v->matrix_column_id === $colDim->id;
                });


               
                // $subOptions = MatrixDimensionOption::where([
                //     "matrix_id" => $matrix->id,
                //     "broker_id" => $brokerId,
                //     "matrix_header_id" => $rowDim->matrix_header_id
                // ])->get()
                //     ->map(function ($option) {
                //         return [
                //             "value" => $option->optionHeaderSlug(),
                //             "label" => $option->optionHeaderTitle()
                //         ];
                //     });


                $cell = [
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

        return response()->json([
            'matrix' => $matrixData,
            'broker_id' => $brokerId,
            'matrix_id' => $matrixId
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('brokers::create');
    }



    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('brokers::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('brokers::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Generate a unique matrix header slug
     * The uniqueness is based on the combination of slug, matrix_id, and broker_id
     * Uses a secure hash to ensure uniqueness without database queries
     * 
     * @param string $title The title to convert to slug
     * @param int $matrixId The matrix ID
     * @param int $brokerId The broker ID
     * @return string
     */
    private function generateMatrixHeaderSlug(string $title, int $matrixId, int $brokerId): string
    {
        // Create a unique string combining all parameters
        $uniqueString = $title . '-' . $matrixId . '-' . $brokerId;
        
        // Generate a hash and take first 8 characters
        $hash = substr(md5($uniqueString), 0, 8);
        
        // Convert title to slug and combine with hash
        $baseSlug = Str::slug($title);
        return $baseSlug . '-' . $hash;
    }

    public function getUniqueSlug($slug,$usedSlugs){
      
        if(in_array($slug, $usedSlugs)){
            $slug = $slug. '-' . count($usedSlugs);
           
        }
        return $slug;
    }

}
// [
//     [
//         {
//             "value": [],
//             "rowHeader": "row-header-2",
//             "colHeader": "trade-mt4",
//             "type": "Text",
//             "selectedRowHeaderSubOptions": [
//                 {
//                     "value": "row-subheader-1",
//                     "label": "Row subheader 1"
//                 },
//                 {
//                     "value": "row-subheader-2",
//                     "label": "Row subheader 2"
//                 }
//             ]
//         },
//         {
//             "value": {
//                 "Number": "jyykyk"
//             },
//             "rowHeader": "row-header-1",
//             "colHeader": "zero-mt4",
//             "type": "Number"
//         },
//         {
//             "value": {
//                 "Number": "34",
//                 "Currency": "lots"
//             },
//             "rowHeader": "row-header-1",
//             "colHeader": "trade-mt5",
//             "type": "NumberWithCurrency"
//         }
//     ]
// ]