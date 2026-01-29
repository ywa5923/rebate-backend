<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Brokers\Transformers\MatrixHeaderResource;
use Modules\Brokers\Services\MatrixHeadearsQueryParser;
use Modules\Brokers\Repositories\MatrixHeaderRepository;

use Illuminate\Support\Str;
use Modules\Brokers\Services\MatrixService;
use Illuminate\Support\Facades\Validator;

class MatrixController extends Controller
{
    
  
    public function __construct(
        protected MatrixService $matrixService
    ) {
        
    }

    public function getHeaders(MatrixHeadearsQueryParser $queryParser, Request $request, MatrixHeaderRepository $rep,$broker_id)
    {
       
       try {
        $queryParser->parse($request);
        // dd( $queryParser->getWhereParams());

        if (empty($queryParser->getWhereParams())) {
            return new Response("not found", 404);
        }

        $columnHeaders = $rep->getColumnHeadearsByType(
            'column',
            $queryParser->getWhereParam("matrix_id") ?? null,
            // $queryParser->getWhereParam("broker_id") ?? null,
            ['broker_id','=',$broker_id],
            $queryParser->getWhereParam("col_group") ?? null,
            $queryParser->getWhereParam("language") ?? null,
            $queryParser->getWhereParam("broker_id_strict")[2] ?? false
        );

        //return $columnHeaders;

        $rowHeaders = $rep->getColumnHeadearsByType(
            'row',
            $queryParser->getWhereParam("matrix_id") ?? null,
            // $queryParser->getWhereParam("broker_id") ?? null,
            ['broker_id','=',$broker_id],
            $queryParser->getWhereParam("row_group") ?? null,
            $queryParser->getWhereParam("language") ?? null,
            $queryParser->getWhereParam("broker_id_strict")[2] ?? false
        );

        // return response()->json([
        //     'success'=>true,
        //     'data'=>[
        //         'columnHeaders' => $columnHeaders,
        //         'rowHeaders' => $rowHeaders
        //     ]
        // ]);

        return response()->json([
            'success'=>true,
            'data'=>[
                'columnHeaders' => MatrixHeaderResource::collection($columnHeaders),
                'rowHeaders' => MatrixHeaderResource::collection($rowHeaders)
            ]
        ]);
        
       } catch (\Exception $e) {
        return response()->json([
            'success'=>false,
            'message' => 'Failed to get headers',
            'error' => $e->getMessage()
        ], 500);
       }
       
    }


    public function store(Request $request,$broker_id)
    {
        $startTime = microtime(true);
        $validator = Validator::make($request->all(), [
            'matrix' => 'array',
           // 'broker_id' => 'required|integer',
            'matrix_name' => 'required|string',
            'zone_id' => 'sometimes|nullable|integer',
           // 'is_admin' => 'sometimes|nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();


        if (empty($data['matrix'])) {
            return response()->json([
                'message' => 'Matrix data is empty, nothing to save',
            ]);
        }
        $matrixName = $data['matrix_name'];
        //$brokerId = $data['broker_id'];
        $zoneId = $data['zone_id'] ?? null;
       // $isAdmin = $data['is_admin'] ?? null;
        $isAdmin=app('isAdmin');

        try {

            $matrixId = $this->matrixService->getMatrixIdByName($matrixName);
            if (!$matrixId) {
                return response()->json([
                    'message' => 'Matrix name not found in the database',
                ], 404);
            }

            $previousMatrixData = $this->matrixService->getFormattedMatrix($matrixName, $broker_id, $zoneId);
            if (!empty($previousMatrixData) && !$isAdmin) {
                //set the previous value in the matrix data only if the admin is not true.
                //admin save in public_value, so we don't need to set the previous value.
                $this->matrixService->setPreviousValueInMatrixData($previousMatrixData, $data['matrix']);
            }
            $result = DB::transaction(function () use ($data, $broker_id, $matrixName, $matrixId, $startTime, $zoneId, $isAdmin) {

            
                //matrix cell's is_updated_entry is used to identify the updated entries and previous values in the matrix data.
                //it is set to 1 in MAtrixService::setPreviousValueInMatrixData if the cell value is different from the previous value.
                //when admin save the matrix, all matrix cells will have is_updated_entry=0. See MatrixHeaderRepository::insertMatrixValues

                $this->matrixService->saveMatrixData(
                    $data['matrix'],
                    $broker_id,
                    $matrixName,
                    $matrixId,
                    $zoneId,
                    $isAdmin
                );

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

    public function getHeaderId($headerSlug, $headers, $onlyParent = false): int|null
    {
        $header = null;
        if ($onlyParent) {
            $header = $headers->firstWhere(function ($header) use ($headerSlug) {
                return $header->slug === $headerSlug && $header->parent_id === null;
            });
        } else {
            $header = $headers->firstWhere(function ($header) use ($headerSlug) {
                return $header->slug === $headerSlug && $header->parent_id !== null;
            });
        }

        return $header ? $header->id : null;
    }

    /**
     * Get the matrix data
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request,$broker_id)
    {

        // if ($request->has('is_admin')) {
        //     $request->merge([
        //         'is_admin' => filter_var($request->query('is_admin'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
        //     ]);
        // }

        $isAdmin=app('isAdmin');

        $validator = Validator::make($request->all(), [
            'zone_id' => 'sometimes|nullable|integer',
            //'broker_id' => 'required|integer',
            'matrix_name' => 'required|string',
            //'is_admin' => 'sometimes|nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $zoneId = $data['zone_id'] ?? null;
       // $is_admin = $data['is_admin'] ?? null;
        //$brokerId = $data['broker_id'];
        $matrixName = $data['matrix_name'];


        if (!$matrixName || !$broker_id) {
            return response()->json(['error' => 'matrix_id and broker_id are required'], 400);
        }

        try {

            $matrixData = $this->matrixService->getFormattedMatrix($matrixName, $broker_id, $zoneId);

            return response()->json([
                'matrix' => $matrixData,
                'broker_id' => $broker_id,
                'matrix_name' => $matrixName
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get matrix data', 'error_message' => $e->getMessage()], 500);
        }
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

    public function getUniqueSlug($slug, $usedSlugs)
    {

        if (in_array($slug, $usedSlugs)) {
            $slug = $slug . '-' . count($usedSlugs);
        }
        return $slug;
    }
}
