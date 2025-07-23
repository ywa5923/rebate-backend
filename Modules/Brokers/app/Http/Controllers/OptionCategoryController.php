<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\OptionCategoryService;
use Modules\Brokers\Transformers\OptionCategoryResource;
use Modules\Brokers\Transformers\OptionCategoryCollection;

/**
 * @OA\Tag(
 *     name="Option Categories",
 *     description="API Endpoints for managing option categories"
 * )
 */
class OptionCategoryController extends Controller
{
    protected OptionCategoryService $service;

    public function __construct(OptionCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of option categories.
     * 
     * @OA\Get(
     *     path="/api/v1/option-categories",
     *     summary="Get all option categories",
     *     tags={"Option Categories"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for name or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="language_code",
     *         in="query",
     *         description="Language code for translations",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "position", "default_language", "created_at", "updated_at"})
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OptionCategory")),
     *             @OA\Property(property="pagination", type="object", nullable=true)
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
         
            $result = $this->service->getOptionCategories($request);
            
            
            $result['data'] = OptionCategoryResource::collection($result['data']);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving option categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created option category.
     * 
     * @OA\Post(
     *     path="/api/v1/option-categories",
     *     summary="Create a new option category",
     *     tags={"Option Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Trading Features"),
     *             @OA\Property(property="description", type="string", example="Trading platform features and capabilities"),
     *             @OA\Property(property="icon", type="string", example="fas fa-chart-line"),
     *             @OA\Property(property="color", type="string", example="#007bff"),
     *             @OA\Property(property="background_color", type="string", example="#f8f9fa"),
     *             @OA\Property(property="border_color", type="string", example="#dee2e6"),
     *             @OA\Property(property="text_color", type="string", example="#212529"),
     *             @OA\Property(property="font_weight", type="string", example="bold"),
     *             @OA\Property(property="position", type="integer", example=1),
     *             @OA\Property(property="default_language", type="string", example="en")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Option category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/OptionCategory"),
     *             @OA\Property(property="message", type="string", example="Option category created successfully")
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
            $optionCategory = $this->service->createOptionCategory($request->all());

            return response()->json([
                'success' => true,
                'data' => new OptionCategoryResource($optionCategory),
                'message' => 'Option category created successfully'
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating option category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified option category.
     * 
     * @OA\Get(
     *     path="/api/v1/option-categories/{id}",
     *     summary="Get a specific option category",
     *     tags={"Option Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Option category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/OptionCategory")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option category not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $optionCategory = $this->service->getOptionCategoryById($id);

            if (!$optionCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Option category not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new OptionCategoryResource($optionCategory)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving option category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified option category.
     * 
     * @OA\Put(
     *     path="/api/v1/option-categories/{id}",
     *     summary="Update an option category",
     *     tags={"Option Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Option category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Trading Features"),
     *             @OA\Property(property="description", type="string", example="Updated trading platform features"),
     *             @OA\Property(property="icon", type="string", example="fas fa-chart-line"),
     *             @OA\Property(property="color", type="string", example="#007bff"),
     *             @OA\Property(property="background_color", type="string", example="#f8f9fa"),
     *             @OA\Property(property="border_color", type="string", example="#dee2e6"),
     *             @OA\Property(property="text_color", type="string", example="#212529"),
     *             @OA\Property(property="font_weight", type="string", example="bold"),
     *             @OA\Property(property="position", type="integer", example=1),
     *             @OA\Property(property="default_language", type="string", example="en")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/OptionCategory"),
     *             @OA\Property(property="message", type="string", example="Option category updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option category not found"
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
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $optionCategory = $this->service->updateOptionCategory($id, $request->all());

            return response()->json([
                'success' => true,
                'data' => new OptionCategoryResource($optionCategory),
                'message' => 'Option category updated successfully'
            ], 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Option category not found'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error updating option category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified option category.
     * 
     * @OA\Delete(
     *     path="/api/v1/option-categories/{id}",
     *     summary="Delete an option category",
     *     tags={"Option Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Option category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option category not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteOptionCategory($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Option category not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Option category deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Option category not found'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error deleting option category: ' . $e->getMessage()
            ], 500);
        }
    }
}
