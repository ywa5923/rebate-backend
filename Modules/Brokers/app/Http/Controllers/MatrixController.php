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

class MatrixController extends Controller
{
    public function getHeaders(MatrixHeadearsQueryParser $queryParser, Request $request, MatrixHeaderRepository $rep)
    {
        $queryParser->parse($request);
        if (empty($queryParser->getWhereParams())) {
            return new Response("not found", 404);
        }

        $columnHeaders = $rep->getColumnHeaders(
            $queryParser->getWhereParam("matrix_id"),
            $queryParser->getWhereParam("broker_id") ?? null,
            $queryParser->getWhereParam("broker_id_strict")[2] ?? false
        );

        //return $columnHeaders;

        $rowHeaders = $rep->getRowHeaders(
            $queryParser->getWhereParam("matrix_id"),
            $queryParser->getWhereParam("broker_id") ?? null,
            $queryParser->getWhereParam("broker_id_strict")[2] ?? false
        );

      
        return [
            'columnHeaders' => MatrixHeaderResource::collection($columnHeaders),
            'rowHeaders' => MatrixHeaderResource::collection($rowHeaders)
        ];
    }

    public function store(Request $request, MatrixHeaderRepository $rep)
    {
        $startTime = microtime(true);

        $data = $request->validate([
            'matrix' => 'array',
            'broker_id' => 'required|integer',
            'matrix_id' => 'required|string',
        ]);

        $matrixName = $data['matrix_id'];
        $brokerId = $data['broker_id'];
        $matrix = Matrix::where("name", "=", $matrixName)->first();

        try {
            // First transaction - Flush data
            DB::beginTransaction();
            $rep->flushMatrix($matrix->id, $brokerId);
            DB::commit();

            if (empty($data['matrix'])) {
                return response()->json([
                    'message' => 'Matrix data was deleted successfully',
                ]);
            }

            // Second transaction - Create headers
            // DB::beginTransaction();
            $allHeaders = $rep->getAllHeaders(["name", "=", $matrixName], null, false);

            $newHeaders = [];
            $newHeadersSubOptions = [];
            $headearsSelectedSubOptions = [];

            // Create all headers first
            foreach ($data['matrix'] as $rowIndex => $row) {
                $selectedRowHeaderSubOptions = $row[0]['selectedRowHeaderSubOptions'] ?? null;
                $rowHeaderSlug = $row[0]['rowHeader'];
                $rowHeaderId = $this->getHeaderId($rowHeaderSlug, $allHeaders);
                //////new version=====================
                $headearExists = in_array($rowHeaderSlug, array_column($newHeaders, 'slug'));
                if ($rowHeaderId == null && !$headearExists){
                    $newHeaders[] = [
                        'title' => ucwords(str_replace('-', ' ', $rowHeaderSlug)),
                        'slug' => $rowHeaderSlug,
                        'broker_id' => $brokerId,
                        'type' => 'row',
                        'matrix_id' => $matrix->id
                    ];
                }
                    
               
                foreach ($selectedRowHeaderSubOptions as $subOption) {
                    $subOptionId = $this->getHeaderId($subOption['value'], $allHeaders);
                    $subOptionExists = in_array($subOption['value'], array_column(array_merge(...array_values($newHeadersSubOptions)), 'slug'));
                    if ($subOptionId == null && !$subOptionExists) {
                        $newHeadersSubOptions[$rowHeaderSlug][]=
                            [
                                'parent_id' => $rowHeaderId,
                                'slug' => $subOption['value'],
                                'title' => $subOption['label'],
                                'broker_id' => $brokerId,
                                'type' => 'row',
                                'matrix_id' => $matrix->id
                            ];
                    }
                    //$headearSelectedSubOptions[$rowHeaderSlug][] = $subOption['value'];
                    $headearsSelectedSubOptions[$rowIndex][] = $subOption['value'];
                }
                   
            } //end of matrix foreach
           !empty($newHeaders) && MatrixHeader::insert($newHeaders);
            //dd($newHeaders);
            //$allHeaders = $rep->getAllHeaders(["name", "=", $matrixName], null, false);
            $brokerHeadears = $rep->getAllHeaders(["name", "=", $matrixName], ['broker_id', '=', $brokerId], false);
           
            foreach ($newHeadersSubOptions as $rowHeaderSlug => &$subOptionArray) {
                $rowHeaderId = $this->getHeaderId($rowHeaderSlug, $brokerHeadears);
                foreach ($subOptionArray as &$subOption) {
                   
                    $subOption['parent_id'] = $rowHeaderId;
                }
                unset($subOption);
            }
           
            unset($subOptionArray); 
            
            MatrixHeader::insert(array_merge(...array_values($newHeadersSubOptions)));


            //grab again all headears
            $allHeaders = $rep->getAllHeaders(["name", "=", $matrixName], ['broker_id', '=', $brokerId], false);
           
            //$rep->insertSelectedSubOptions($headearSelectedSubOptions, $allHeaders, $matrix->id, $brokerId);

        } catch (\Exception $e) {
            // Log::error('Matrix store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save matrix data',
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
                $rowHeaderId = $this->getHeaderId($rowHeaderSlug, $allHeaders);

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
                    $colHeaderId = $this->getHeaderId($colHeaderSlug, $allHeaders);

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
                        'value' => json_encode($cell['value'])
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
            $rep->insertDimensionOptions($headearsSelectedSubOptions, $rowDimIds, $matrix->id, $brokerId,  $allHeaders);
 

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

    public function getHeaderId($headerSlug, $headers)
    {
        $header = $headers->firstWhere('slug', $headerSlug);
        return $header ? $header->id : null;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $matrixId = $request->query('matrix_id');
        $brokerId = $request->query('broker_id');

        $matrixId = "Matrix-1";
        $brokerId = 1;

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