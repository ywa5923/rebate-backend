<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Brokers\Http\Requests\OptionCategoryListRequest;
use Modules\Brokers\Services\OptionCategoryService;
use Modules\Brokers\Transformers\OptionCategoryResource;


class OptionCategoryController extends Controller
{
    protected OptionCategoryService $service;

    public function __construct(OptionCategoryService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {

            $result = $this->service->getOptionCategories($request);

            $result['data'] = OptionCategoryResource::collection($result['data']);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving option categories: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new option category
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $optionCategory = $this->service->createOptionCategory($request->all());

            return response()->json([
                'success' => true,
                'data' => new OptionCategoryResource($optionCategory),
                'message' => 'Option category created successfully',
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating option category: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get option categories list.
     * Return a collection of option categories without relations.
     */
    public function getOptionCategoriesList(OptionCategoryListRequest $request): Response
    {
        try {
            $filters = $request->getFilters();
            $orderBy = $request->getOrderBy();
            $orderDirection = $request->getOrderDirection();
            $perPage = $request->getPerPage();

            $optionCategories = $this->service->getOptionCategoriesList($filters, $orderBy, $orderDirection, $perPage);

            return new Response(json_encode([
                'success' => true,
                'data' => $optionCategories->items(),
                'pagination' => [
                    'current_page' => $optionCategories->currentPage(),
                    'last_page' => $optionCategories->lastPage(),
                    'per_page' => $optionCategories->perPage(),
                    'total' => $optionCategories->total(),
                    'from' => $optionCategories->firstItem(),
                    'to' => $optionCategories->lastItem(),
                ],
            ]), 200);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'success' => false,
                'error' => 'Error getting option categories list',
                'message' => $e->getMessage(),
            ]), 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $optionCategory = $this->service->updateOptionCategory($id, $request->all());

            return response()->json([
                'success' => true,
                'data' => new OptionCategoryResource($optionCategory),
                'message' => 'Option category updated successfully',
            ], 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Option category not found',
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error updating option category: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteOptionCategory($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Option category not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Option category deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Option category not found',
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error deleting option category: '.$e->getMessage(),
            ], 500);
        }
    }
}
