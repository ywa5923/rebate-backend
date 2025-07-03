<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\RegulatorService;
use Modules\Brokers\Transformers\RegualtorResource;

class RegulatorController extends Controller
{
    protected RegulatorService $regulatorService;

    public function __construct(RegulatorService $regulatorService)
    {
        $this->regulatorService = $regulatorService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/regulators",
     *     tags={"Regulator"},
     *     summary="Get all regulators",
     *     @OA\Parameter(
     *         name="country",
     *         in="query",
     *         description="Filter by country",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"published", "pending", "rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="enforced",
     *         in="query",
     *         description="Filter by enforced status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, abbreviation, or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="language_code",
     *         in="query",
     *         description="Language code for translations",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Regulator")),
     *             @OA\Property(property="pagination", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="total", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $result = $this->regulatorService->getRegulators($request);
          
            // Transform the data collection
            $result['data'] = RegualtorResource::collection($result['data']);
            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve regulators',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/regulators/create",
     *     tags={"Regulator"},
     *     summary="Get form data for creating regulator",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Create form endpoint"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="statuses", type="array", @OA\Items(type="string")), @OA\Property(property="countries", type="array", @OA\Items(type="string")))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function create(): JsonResponse
    {
        try {
            $formData = $this->regulatorService->getFormData();
            
            return response()->json([
                'success' => true,
                'message' => 'Create form endpoint',
                'data' => $formData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get form data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/regulators",
     *     tags={"Regulator"},
     *     summary="Create a new regulator",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Financial Conduct Authority"),
     *             @OA\Property(property="abreviation", type="string", example="FCA"),
     *             @OA\Property(property="country", type="string", example="United Kingdom"),
     *             @OA\Property(property="country_p", type="string", example="UK"),
     *             @OA\Property(property="description", type="string", example="Financial regulator in the UK"),
     *             @OA\Property(property="description_p", type="string", example="UK financial regulator"),
     *             @OA\Property(property="rating", type="number", format="float", example=4.5),
     *             @OA\Property(property="rating_p", type="number", format="float", example=4.5),
     *             @OA\Property(property="capitalization", type="string", example="High capitalization requirements"),
     *             @OA\Property(property="capitalization_p", type="string", example="High cap requirements"),
     *             @OA\Property(property="segregated_clients_money", type="string", example="Yes"),
     *             @OA\Property(property="segregated_clients_money_p", type="string", example="Yes"),
     *             @OA\Property(property="deposit_compensation_scheme", type="string", example="FSCS protection"),
     *             @OA\Property(property="deposit_compensation_scheme_p", type="string", example="FSCS"),
     *             @OA\Property(property="negative_balance_protection", type="string", example="Yes"),
     *             @OA\Property(property="negative_balance_protection_p", type="string", example="Yes"),
     *             @OA\Property(property="rebates", type="boolean", example=true),
     *             @OA\Property(property="rebates_p", type="boolean", example=true),
     *             @OA\Property(property="enforced", type="boolean", example=true),
     *             @OA\Property(property="enforced_p", type="boolean", example=true),
     *             @OA\Property(property="max_leverage", type="string", example="1:30"),
     *             @OA\Property(property="max_leverage_p", type="string", example="1:30"),
     *             @OA\Property(property="website", type="string", example="https://www.fca.org.uk"),
     *             @OA\Property(property="website_p", type="string", example="https://www.fca.org.uk"),
     *             @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}, example="published"),
     *             @OA\Property(property="status_reason", type="string", example="Approved regulator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Regulator created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Regulator created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Regulator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->regulatorService->validateRegulatorData($request->all());
            $regulator = $this->regulatorService->createRegulator($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Regulator created successfully',
                'data' => new RegualtorResource($regulator)
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create regulator',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/regulators/{id}",
     *     tags={"Regulator"},
     *     summary="Get regulator by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Regulator ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Regulator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Regulator not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $regulator = $this->regulatorService->getRegulatorById($id);

            if (!$regulator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Regulator not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new RegualtorResource($regulator)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve regulator',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/regulators/{id}",
     *     tags={"Regulator"},
     *     summary="Update regulator",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Regulator ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Financial Conduct Authority"),
     *             @OA\Property(property="abreviation", type="string", example="FCA"),
     *             @OA\Property(property="country", type="string", example="United Kingdom"),
     *             @OA\Property(property="country_p", type="string", example="UK"),
     *             @OA\Property(property="description", type="string", example="Updated financial regulator in the UK"),
     *             @OA\Property(property="description_p", type="string", example="Updated UK financial regulator"),
     *             @OA\Property(property="rating", type="number", format="float", example=4.8),
     *             @OA\Property(property="rating_p", type="number", format="float", example=4.8),
     *             @OA\Property(property="capitalization", type="string", example="Updated high capitalization requirements"),
     *             @OA\Property(property="capitalization_p", type="string", example="Updated high cap requirements"),
     *             @OA\Property(property="segregated_clients_money", type="string", example="Yes"),
     *             @OA\Property(property="segregated_clients_money_p", type="string", example="Yes"),
     *             @OA\Property(property="deposit_compensation_scheme", type="string", example="FSCS protection"),
     *             @OA\Property(property="deposit_compensation_scheme_p", type="string", example="FSCS"),
     *             @OA\Property(property="negative_balance_protection", type="string", example="Yes"),
     *             @OA\Property(property="negative_balance_protection_p", type="string", example="Yes"),
     *             @OA\Property(property="rebates", type="boolean", example=true),
     *             @OA\Property(property="rebates_p", type="boolean", example=true),
     *             @OA\Property(property="enforced", type="boolean", example=true),
     *             @OA\Property(property="enforced_p", type="boolean", example=true),
     *             @OA\Property(property="max_leverage", type="string", example="1:30"),
     *             @OA\Property(property="max_leverage_p", type="string", example="1:30"),
     *             @OA\Property(property="website", type="string", example="https://www.fca.org.uk"),
     *             @OA\Property(property="website_p", type="string", example="https://www.fca.org.uk"),
     *             @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}, example="published"),
     *             @OA\Property(property="status_reason", type="string", example="Updated approved regulator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Regulator updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Regulator updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Regulator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Regulator not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validatedData = $this->regulatorService->validateRegulatorData($request->all(), true);
            $regulator = $this->regulatorService->updateRegulator($id, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Regulator updated successfully',
                'data' => new RegualtorResource($regulator)
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update regulator',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/regulators/{id}",
     *     tags={"Regulator"},
     *     summary="Delete regulator",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Regulator ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Regulator deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Regulator deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Regulator not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $deleted = $this->regulatorService->deleteRegulator($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Regulator not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Regulator deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete regulator',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
