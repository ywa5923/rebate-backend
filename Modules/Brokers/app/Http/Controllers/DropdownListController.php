<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\DropdownListService;
use Modules\Brokers\Transformers\DropdownListResource;
use Modules\Brokers\Http\Requests\DropdownListRequest;
use Modules\Brokers\Http\Requests\StoreDropdownListRequest;
use Modules\Brokers\Http\Requests\UpdateDropdownListRequest;

class DropdownListController extends Controller
{
    protected DropdownListService $dropdownListService;

    public function __construct(DropdownListService $dropdownListService)
    {
        $this->dropdownListService = $dropdownListService;
    }

    /**
     * Display a listing of dropdown categories.
     */
    public function index(DropdownListRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $perPage = $validated['per_page'] ?? 15;
            $sortBy = $validated['sort_by'] ?? 'name';
            $sortDirection = $validated['sort_direction'] ?? 'asc';

            // Collect filters
            $filters = [
                'description' => $validated['description'] ?? null,
                'name' => $validated['name'] ?? null,
                'slug' => $validated['slug'] ?? null,
            ];

          

            $lists = $this->dropdownListService->getLists($filters, $sortBy, $sortDirection, $perPage);
            
            return response()->json([
                'success' => true,
                'data' => DropdownListResource::collection($lists->items()),
                'pagination' => [
                    'current_page' => $lists->currentPage(),
                    'last_page' => $lists->lastPage(),
                    'per_page' => $lists->perPage(),
                    'total' => $lists->total(),
                    'from' => $lists->firstItem(),
                    'to' => $lists->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dropdown categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified dropdown list.
     */
    public function showList($id): JsonResponse
    {
        try {
            $list = $this->dropdownListService->getListById($id);
            return response()->json([
                'success' => true,
                'data' => new DropdownListResource($list)
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dropdown list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created dropdown list.
     */
    public function storeList(StoreDropdownListRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $list = $this->dropdownListService->createList($data);
            return response()->json([
                'success' => true,
                'message' => 'Dropdown list created successfully',
                'data' => new DropdownListResource($list)
            ], 201);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create dropdown list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified dropdown list.
     */
    public function updateList(UpdateDropdownListRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $list = $this->dropdownListService->updateList($id, $data);
            return response()->json([
                'success' => true,
                'message' => 'Dropdown list updated successfully',
                'data' => new DropdownListResource($list)
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update dropdown list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the specified dropdown list.
     */
    public function deleteList($id): JsonResponse
    {
        try {
            $this->dropdownListService->deleteList($id);
            return response()->json([
                'success' => true,
                'message' => 'Dropdown list deleted successfully'
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete dropdown list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    
}
