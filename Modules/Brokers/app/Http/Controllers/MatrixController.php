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
        //["broker_id", "=", $brokerId]
        

        try {
            // First transaction for headers
            DB::beginTransaction();

            //flush matrix data first
            $rep->flushMatrix($matrix->id, $brokerId);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Matrix store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete matrix related data',
                'error' => $e->getMessage()
            ], 500);
        }

        if(empty($data['matrix'])) {
              
            return response()->json([
                'message' => 'Matrix data was deleted successfully',
            ]);
        }

        $allHeaders = $rep->getAllHeaders(["name", "=", $matrixName],null,false);
       
        try {
         
            DB::beginTransaction();
            // Create all headers first
            $rowHeaderSubOptionsIds = [];
            foreach ($data['matrix'] as $rowIndex => $row) {
                $selectedRowHeaderSubOptions = $row[0]['selectedRowHeaderSubOptions'] ?? null;
                $rowHeaderSlug = $row[0]['rowHeader'];
                $rowHeaderId = $this->getHeaderId($rowHeaderSlug, $allHeaders);
               
                if ($rowHeaderId == null) {
                    //if row header not found, create it
                    $rowHeaderId = MatrixHeader::insertGetId([
                        'title' => ucwords(str_replace('-', ' ', $rowHeaderSlug)),
                        'slug' => $rowHeaderSlug,
                        'broker_id' => $brokerId,
                        'type' => 'row',
                        'matrix_id' => $matrix->id
                    ]);

                    $rowHeaderSubOptions = [];
                    foreach ($selectedRowHeaderSubOptions as $subOption) {
                        $rowHeaderSubOptions[] = [
                            'parent_id' => $rowHeaderId,
                            'slug' => $subOption['value'],
                            'title' => $subOption['label'],
                            'broker_id' => $brokerId,
                            'type' => 'row',
                            'matrix_id' => $matrix->id
                        ];
                    }
                    MatrixHeader::insert($rowHeaderSubOptions);
                    $rowHeaderSubOptionsIds[$rowHeaderId] = MatrixHeader::where([
                        'parent_id' => $rowHeaderId,
                        'broker_id' => $brokerId,
                        'matrix_id' => $matrix->id,
                        'type' => 'row'
                    ])->pluck('id')->toArray();
                } else {
                    foreach ($selectedRowHeaderSubOptions as $subOption) {
                        $id = $this->getHeaderId($subOption['value'], $allHeaders);
                        if ($id != null) {
                            $rowHeaderSubOptionsIds[$rowHeaderId][] = $id;
                        } else {

                            //to be optimized
                            $subOptionId = MatrixHeader::insertGetId([
                                'parent_id' => $rowHeaderId,
                                'slug' => $subOption['value'],
                                'title' => $subOption['label'],
                                'broker_id' => $brokerId,
                                'type' => 'row',
                                'matrix_id' => $matrix->id
                            ]);
                            $rowHeaderSubOptionsIds[$rowHeaderId][] = $subOptionId;
                        }
                    }
                }
            }

            // Commit first transaction to ensure all headers exist
            DB::commit();

            // Start second transaction for dimensions and values
            DB::beginTransaction();

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

                    if(!isset($columnDimensions[$cellIndex])) {
                        $columnDimensions[$cellIndex] = [
                            'matrix_id' => $matrix->id,
                            'broker_id' => $brokerId,
                            'order' => $cellIndex,
                            'matrix_header_id' => $colHeaderId,
                            'type' => 'column'
                        ];
                    }

                    if($colHeaderId == null) {
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

            // Bulk insert values
            MatrixValue::insert($matrixValues);

            // Create header options (now safe because headers exist)
            foreach ($rowHeaderSubOptionsIds as $rowHeaderId => $subOptionIds) {
                $data = [];
                foreach ($subOptionIds as $subOptionId) {
                    $data[] = [
                        'matrix_id' => $matrix->id,
                        'broker_id' => $brokerId,
                        'matrix_header_id' => $rowHeaderId,
                        'sub_option_id' => $subOptionId,
                    ];
                }
                MatrixHeaderOption::insert($data);
            }

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
            Log::error('Matrix store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save matrix data',
                'error' => $e->getMessage()
            ], 500);
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
            ->with(['matrixHeader'])
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


                $subOptions=MatrixHeaderOption::where([
                    "matrix_id"=>$matrix->id,
                    "broker_id"=>$brokerId,
                    "matrix_header_id"=>$rowDim->matrix_header_id
                ])->get()
                ->map(function($option){
                    return [
                        "value"=>$option->optionHeaderSlug(),
                        "label"=>$option->optionHeaderTitle()
                    ];
                });
           

                $cell = [
                    'value' => $value ? json_decode($value->value, true) : null,
                    'rowHeader' => $rowDim->matrixHeader->slug,
                    'colHeader' => $colDim->matrixHeader->slug,
                    'type' => $colDim->matrixHeader->formType->name ?? 'undefined'
                    //'selectedRowHeaderSubOptions' => $subOptions->toArray()
                ];

                $colIndex==0 && $cell['selectedRowHeaderSubOptions']=$subOptions->toArray();

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