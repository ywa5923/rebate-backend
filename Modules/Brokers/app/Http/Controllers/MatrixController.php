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
use Modules\Brokers\Services\MatrixService;
use Illuminate\Support\Facades\Validator;

class MatrixController extends Controller
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
  
    
    public function store(Request $request, MatrixService $matrixService)
    {
        $startTime = microtime(true);
        $data = $request->validate([
            'matrix' => 'array',
            'broker_id' => 'required|integer',
            'matrix_id' => 'required|string',
        ]);
        if (empty($data['matrix'])) {
            return response()->json([
                'message' => 'Matrix data is empty, nothing to save',
            ]);
        }
        $matrixName = $data['matrix_id'];
        $brokerId = $data['broker_id'];
      //  $matrix = Matrix::where("name", "=", $matrixName)->first();
     
        try {
            $matrix = Matrix::where("name", "=", $matrixName)->first();
            if(!$matrix){
                return response()->json([
                    'message' => 'Matrix name not found in the database',
                ], 404);
            }
            $previousMatrixResponse = $this->index($request);
            $previousMatrixData = json_decode($previousMatrixResponse->getContent(), true);

            if($previousMatrixData['matrix']){
                //$this->compareMatrixData($previousMatrixData['matrix'], $data['matrix']);
                $matrixService->setPreviousValueInMatrixData($previousMatrixData['matrix'], $data['matrix']);
            }
            $result = DB::transaction(function () use ($matrixService, $data, $brokerId, $matrixName, $matrix, $startTime) {
            $matrixService->saveMatrixData($data['matrix'], $brokerId, $matrixName, $matrix->id,null);
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;

            return [
                'message' => 'Matrix data saved successfully',
                'performance' => [
                    'execution_time_ms' => $executionTime,
                    'rows_count' => count($data['matrix'])        
                ]
            ];
        });
        return response()->json($result);
       
        } catch (\Exception $e) {
            // Log::error('Matrix store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save matrix data2',
                'error' => $e->getMessage()
            ], 500);
        
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


       // dd($request->all());
        // Normalize is_admin from string to boolean/null for query params like is_admin=false
        if ($request->has('is_admin')) {
            $request->merge([
                'is_admin' => filter_var($request->query('is_admin'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            ]);
        }

        $validator = Validator::make($request->all(), [
            'zone_id' => 'sometimes|nullable|integer',
            'broker_id' => 'required|integer',
            'matrix_id' => 'required|string',
            'is_admin' => 'sometimes|nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $data['zone_id'] = $data['zone_id'] ?? null;
        $data['is_admin'] = $data['is_admin'] ?? null;

        // $matrixId = $request->query('matrix_id');
        // $brokerId = $request->query('broker_id');
        // $is_admin = $request->query('is_admin');
        //$matrixId = "Matrix-1";
        //$brokerId = 181;

        $matrixId = $data['matrix_id'];
        $brokerId = $data['broker_id'];
        $is_admin = $data['is_admin'];
        $zoneId = $data['zone_id'];
        
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
            ->where(function($query) use ($zoneId){
                $query->where('zone_id', $zoneId)
                ->orWhere('is_invariant', 1);
            })

            ->get();

        // if($zoneId){
        // $values = MatrixValue::where('matrix_id', $matrix->id)
        //     ->where('broker_id', $brokerId)
        //     ->where(function($query) use ($zoneId){
        //         $query->where('zone_id', $zoneId)
        //         ->orWhere('is_invariant', 1);
        //     })

        //     ->get();
        // }else{
        //     $values = MatrixValue::where('matrix_id', $matrix->id)
        //     ->where('broker_id', $brokerId)
        //     ->where('is_invariant', 1)
        //     ->get();
        // }

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
       // return redirect()->back();
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