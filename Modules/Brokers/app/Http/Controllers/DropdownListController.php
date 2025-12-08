<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\DropdownListService;
use Modules\Brokers\Transformers\DropdownListResource;
use Modules\Brokers\Http\Requests\DropdownListRequest;
use Modules\Brokers\Http\Requests\StoreDropdownListRequest;
use Modules\Brokers\Http\Requests\UpdateDropdownListRequest;
use Modules\Brokers\Tables\DropdownListTableConfig;
use Modules\Brokers\Forms\DropdownListForm;
use Modules\Brokers\Transformers\DropdownListCollection;
class DropdownListController extends Controller
{
    

    public function __construct( 
        private readonly DropdownListService $dropdownListService,
        private readonly DropdownListTableConfig $tableConfig,
        private readonly DropdownListForm $formConfig
    )
    {
        
    }

    

    /**
     * Display a listing of dropdown categories.
     */
    public function index(DropdownListRequest $request): JsonResponse
    {
        try {
            // $validated = $request->validated();
            
            // $perPage = $validated['per_page'] ?? 15;
            // $orderBy = $validated['order_by'] ?? 'name';
            // $orderDirection = $validated['order_direction'] ?? 'asc';

            // // Collect filters
            // $filters = [
            //     'description' => $validated['description'] ?? null,
            //     'name' => $validated['name'] ?? null,
            //     'slug' => $validated['slug'] ?? null,
            // ];
            $filters = $request->getFilters();
            $orderBy = $request->getOrderBy();
            $orderDirection = $request->getOrderDirection();
            $perPage = $request->getPerPage();

          

            $lists = $this->dropdownListService->getLists($filters, $orderBy, $orderDirection, $perPage);
            
            return response()->json([
                'success' => true,
                'data' => DropdownListResource::collection($lists->items()),
                'table_columns_config' => $this->tableConfig->columns(),
                'filters_config'=>$this->tableConfig->filters(),
                'form_config'=> $this->formConfig->getFormData(),
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

    public function getFormConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->formConfig->getFormData()
        ], 200);
    }

    /**
     * Display the specified dropdown list.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $dropdownList = $this->dropdownListService->getListById($id);
         
            return response()->json([
                'success' => true,
                'data' => (new DropdownListResource($dropdownList))->additional(['detail' => 'form-edit']),
              // 'data'=>(new DropdownListCollection($dropdownList->items(), ['detail' => 'table-list'])),
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
    public function store(StoreDropdownListRequest $request): JsonResponse
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
    public function update(UpdateDropdownListRequest $request, int $id): JsonResponse
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
    public function delete(int $id): JsonResponse
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
