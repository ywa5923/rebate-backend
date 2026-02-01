<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Transformers\MatrixHeaderResource;
use Modules\Brokers\Repositories\MatrixHeaderRepository;
use Modules\Brokers\Services\MatrixService;
use Illuminate\Support\Facades\Validator;

class MatrixController extends Controller
{

    public function __construct(
        protected MatrixService $matrixService
    ) {}

    /**
     * Get the matrix headers for a broker
     * @param Request $request
     * @param MatrixHeaderRepository $rep
     * @param int $broker_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getHeaders(Request $request, MatrixHeaderRepository $rep, $broker_id)
    {
        //when broker_strict_id is true, the action return the broker's matrix headers only.
        //when broker_strict_id is false, the action return the matrix headers where broker_id is null or the broker_id is the same as the broker_id in the request
        try {
            $validatedData = $request->validate([
                'matrix_id' => 'sometimes|string|max:145',
                'with_account_type_columns' => 'sometimes|boolean',
                'col_group' => 'sometimes|string|max:145',
                'row_group' => 'sometimes|string|max:145',
                'broker_id_strict' => 'sometimes|boolean',
                'language' => 'sometimes|string|max:15',
            ]);
            $withAccountTypeColumns=(bool)($validatedData['with_account_type_columns'] ?? false);

            $columnHeaders = $withAccountTypeColumns ?
                $rep->getAccountTypesColumnHeaders($broker_id) :
                MatrixHeaderResource::collection($rep->getHeadearsByType(
                    'column', 
                    $validatedData['matrix_id'] ?? null, 
                    $broker_id,
                    $validatedData['col_group'] ?? null,
                    $validatedData['language'], 
                    $validatedData['broker_id_strict']??false));

            $rowHeaders = $rep->getHeadearsByType(
                'row',
                $validatedData['matrix_id'] ?? null,
                $broker_id,
                $validatedData['row_group'] ?? null,
                $validatedData['language'],
                $validatedData['broker_id_strict']??false
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'columnHeaders' => $columnHeaders,
                    'rowHeaders' => MatrixHeaderResource::collection($rowHeaders)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get headers',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Save/update the broker's matrix data
     * @param Request $request
     * @param int $broker_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request, $broker_id)
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
        $isAdmin = app('isAdmin');

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
                    'success' => true,
                    'message' => 'Matrix data saved successfully. Execution time: ' . $executionTime . 'ms',
                ];
            });
            return response()->json($result);
        } catch (\Exception $e) {
            // Log::error('Matrix store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save matrix data2',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the matrix data for a broker
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $broker_id)
    {
        $isAdmin = app('isAdmin');
        $validator = Validator::make($request->all(), [
            'zone_id' => 'sometimes|nullable|integer',
            'matrix_name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $zoneId = $data['zone_id'] ?? null;

        $matrixName = $data['matrix_name'];

        if (!$matrixName || !$broker_id) {
            return response()->json(['error' => 'matrix_id and broker_id are required'], 400);
        }

        try {
            $matrixData = $this->matrixService->getFormattedMatrix($matrixName, $broker_id, $zoneId);
            return response()->json([
                'success' => true,
                'data' => $matrixData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get matrix data. Error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
