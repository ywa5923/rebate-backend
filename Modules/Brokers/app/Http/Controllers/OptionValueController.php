<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\OptionValueService;
use Modules\Brokers\Transformers\OptionValueResource;
use Modules\Brokers\Models\MatrixHeader;
use App\Utilities\ModelHelper;
use Illuminate\Support\Facades\DB;

class OptionValueController extends Controller
{
    protected OptionValueService $optionValueService;

    public function __construct(OptionValueService $optionValueService)
    {
        $this->optionValueService = $optionValueService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/option-values",
     *     tags={"OptionValue"},
     *     summary="Get all option values",
     *     @OA\Parameter(
     *         name="broker_id",
     *         in="query",
     *         description="Filter by broker ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="broker_option_id",
     *         in="query",
     *         description="Filter by broker option ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="option_slug",
     *         in="query",
     *         description="Filter by option slug",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in value, public_value, and option_slug",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"option_slug", "value", "status", "created_at", "updated_at"})
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
     *         name="page",
     *         in="query",
     *         description="Page number",
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
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="total", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $result = $this->optionValueService->getOptionValues($request);
          
            // Transform the data collection
            $result['data'] = OptionValueResource::collection($result['data']);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve option values',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/option-values/create",
     *     tags={"OptionValue"},
     *     summary="Get form data for creating option value",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Create form endpoint"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="brokers", type="array", @OA\Items(type="object")), @OA\Property(property="broker_options", type="array", @OA\Items(type="object")), @OA\Property(property="zones", type="array", @OA\Items(type="object")), @OA\Property(property="status_options", type="object"), @OA\Property(property="type_options", type="object"))
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
            $formData = $this->optionValueService->getFormData();

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
     *     path="/api/v1/option-values",
     *     tags={"OptionValue"},
     *     summary="Create a new option value",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="option_slug", type="string", example="minimum_deposit", maxLength=255),
     *             @OA\Property(property="value", type="string", example="100"),
     *             @OA\Property(property="public_value", type="string", example="$100"),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="status_message", type="string", example="Active option", maxLength=1000),
     *             @OA\Property(property="default_loading", type="boolean", example=true),
     *             @OA\Property(property="type", type="string", example="number", maxLength=100),
     *             @OA\Property(property="metadata", type="object", example={"unit": "USD", "currency": "USD"}),
     *             @OA\Property(property="is_invariant", type="boolean", example=true),
     *             @OA\Property(property="delete_by_system", type="boolean", example=false),
     *             @OA\Property(property="broker_id", type="integer", example=1),
     *             @OA\Property(property="broker_option_id", type="integer", example=1),
     *             @OA\Property(property="zone_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Option value created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option value created successfully"),
     *             @OA\Property(property="data", type="object")
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
            
            // Validate data
            $validatedData = $this->optionValueService->validateOptionValueData($request->all());
            
            
            // Create option value
            $optionValue = $this->optionValueService->createOptionValue($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Option value created successfully',
                'data' => new OptionValueResource($optionValue),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create option value',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/brokers/{broker_id}/option-values",
     *     tags={"OptionValue"},
     *     summary="Create multiple option values for a broker",
     *     @OA\Parameter(
     *         name="broker_id",
     *         in="path",
     *         description="Broker ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="option_values", type="array", @OA\Items(
     *                 @OA\Property(property="option_slug", type="string", example="minimum_deposit"),
     *                 @OA\Property(property="value", type="string", example="100"),
     *                 @OA\Property(property="public_value", type="string", example="$100"),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="broker_option_id", type="integer", example=1)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Option values created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option values created successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
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
    public function storeMultiple(Request $request, int $brokerId): JsonResponse
    {
        //Example request
        // {
        //     "option_values": [
        //       {
        //         "option_slug": "account_name",
        //         "value": "swsws",
        //         "metadata": null
        //       }
        //     ],
        //     "entity_id": 0,
        //     "entity_type": "AccountType"
        //   }
      
        $entityType = $request->input('entity_type', null);
        if (!$brokerId) {
            return response()->json([
                'success' => false,
                'message' => 'broker_id, entity_id, and entity_type are all required.'
            ], 422);
        }
        try {
            DB::transaction(function () use ($request, $brokerId,$entityType) {
            // Validate data
            $validatedData = $this->optionValueService->validateMultipleOptionValuesData($request->input('option_values', []));
           
            $modelClass=is_null($entityType) ? $this->optionValueService->getModelClassFromSlug('Broker') : $this->optionValueService->getModelClassFromSlug($entityType);
            $entityId=is_null($entityType) || $entityType=='Broker' ? $brokerId : $this->optionValueService->saveModelInstance($modelClass,$brokerId);
            $this->optionValueService->createMultipleOptionValues($brokerId, $modelClass,$entityId,$validatedData);

            if(str_contains($modelClass,'AccountType')){
                $accountNameOption= array_filter(  $validatedData, function($optionValue){
                    return $optionValue['option_slug']=='account_type_name';
                });
              
                $accountName= $accountNameOption[0]['value'];

                $this->optionValueService->createMatrixHeader([
                    'title' => $accountName,
                    'slug' => strtolower(str_replace(' ', '-', $accountName)),
                    'broker_id' => $brokerId,
                    'type' => 'column',
                    'is_invariant' => true,
                    'parent_id' => null,
                    'form_type_id' => 4,
                    'matrix_id' => 1,
                ]);
                }
            });
            

            return response()->json([
                'success' => true,
                'message' => 'Option values created successfully',
              //  'data' => $optionValues, // Return the array directly since it's already formatted
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create option values',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/option-values/{id}",
     *     tags={"OptionValue"},
     *     summary="Get option value by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Option value ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option value not found"
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
            $optionValue = $this->optionValueService->getOptionValueById($id);

            if (!$optionValue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Option value not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new OptionValueResource($optionValue)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve option value',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/option-values/{id}/edit",
     *     tags={"OptionValue"},
     *     summary="Get form data for editing option value",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Option value ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Edit form endpoint"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="option_value", type="object"), @OA\Property(property="form_data", type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option value not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function edit($id): JsonResponse
    {
        try {
            $optionValue = $this->optionValueService->getOptionValueById($id);

            if (!$optionValue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Option value not found'
                ], 404);
            }

            $formData = $this->optionValueService->getFormData();

            return response()->json([
                'success' => true,
                'message' => 'Edit form endpoint',
                'data' => [
                    'option_value' => new OptionValueResource($optionValue),
                    'form_data' => $formData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get edit form data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/option-values/{id}",
     *     tags={"OptionValue"},
     *     summary="Update option value",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Option value ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="option_slug", type="string", example="minimum_deposit", maxLength=255),
     *             @OA\Property(property="value", type="string", example="200"),
     *             @OA\Property(property="public_value", type="string", example="$200"),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="status_message", type="string", example="Updated option", maxLength=1000),
     *             @OA\Property(property="default_loading", type="boolean", example=true),
     *             @OA\Property(property="type", type="string", example="number", maxLength=100),
     *             @OA\Property(property="metadata", type="object", example={"unit": "USD", "currency": "USD"}),
     *             @OA\Property(property="is_invariant", type="boolean", example=true),
     *             @OA\Property(property="delete_by_system", type="boolean", example=false),
     *             @OA\Property(property="broker_id", type="integer", example=1),
     *             @OA\Property(property="broker_option_id", type="integer", example=1),
     *             @OA\Property(property="zone_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option value updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option value updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option value not found"
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
            // Validate data
            $validatedData = $this->optionValueService->validateOptionValueData($request->all(), true);
            
            // Update option value
            $optionValue = $this->optionValueService->updateOptionValue($id, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Option value updated successfully',
                'data' => new OptionValueResource($optionValue)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update option value',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/brokers/{broker_id}/option-values",
     *     tags={"OptionValue"},
     *     summary="Update multiple option values for a broker",
     *     @OA\Parameter(
     *         name="broker_id",
     *         in="path",
     *         description="Broker ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="option_values", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="option_slug", type="string", example="minimum_deposit"),
     *                 @OA\Property(property="value", type="string", example="200"),
     *                 @OA\Property(property="public_value", type="string", example="$200"),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="broker_option_id", type="integer", example=1)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option values updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option values updated successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
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
    public function updateMultiple(Request $request, int $brokerId): JsonResponse
    {
        //TODO:CHECK IF IS ADMIN IS TRUE WITH THE AUTH MIDDLEWARE
        $isAdmin=true;
   
        try {
            $entityId = $request->input('entity_id');
            $entityType = $request->input('entity_type');
            $optionValuesData = $request->input('option_values', []);
           

            $this->optionValueService->validateEntityTypeAndId($entityType, $entityId, $brokerId, $isAdmin);

            $this->optionValueService->validateMultipleOptionValuesData($optionValuesData, true);
         
            $optionValues = $this->optionValueService->updateMultipleOptionValues($isAdmin, $brokerId, $entityId, $entityType,  $optionValuesData);
          
            return response()->json([
                'success' => true,
                'message' => 'Option values updated successfully',
                'data' => $optionValues
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update option values',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/option-values/{id}",
     *     tags={"OptionValue"},
     *     summary="Delete option value",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Option value ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option value deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option value deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option value not found"
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
            $this->optionValueService->deleteOptionValue($id);

            return response()->json([
                'success' => true,
                'message' => 'Option value deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete option value',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
