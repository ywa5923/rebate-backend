<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Brokers\Forms\BrokerGroupForm;
use Modules\Brokers\Http\Requests\BrokerGroupListRequest;
use Modules\Brokers\Http\Requests\BrokerGroupStoreRequest;
use Modules\Brokers\Services\BrokerGroupService;
use Modules\Brokers\Tables\BrokerGroupTableConfig;
use Modules\Brokers\Transformers\BrokerGroupResource;

class BrokerGroupController extends Controller
{
    public function __construct(
        private BrokerGroupService $brokerGroupService,
    ) {
    }

    public function index(BrokerGroupListRequest $request, BrokerGroupForm $formConfig, BrokerGroupTableConfig $tableConfig): JsonResponse
    {

        $filters = $request->getFilters();
        $orderBy = $request->getOrderBy();
        $orderDirection = $request->getOrderDirection();
        $perPage = $request->getPerPage();

        $brokerGroups = $this->brokerGroupService->getBrokerGroupList($perPage, $orderBy, $orderDirection, $filters);

        return response()->json([
            'success' => true,
            'data' => BrokerGroupResource::collection($brokerGroups->items()),
            'form_config' => $formConfig->getFormData(),
            'table_columns_config' => $tableConfig->columns(),
            'filters_config' => $tableConfig->filters(),
            'pagination' => [
                'current_page' => $brokerGroups->currentPage(),
                'last_page' => $brokerGroups->lastPage(),
                'per_page' => $brokerGroups->perPage(),
                'total' => $brokerGroups->total(),
                'from' => $brokerGroups->firstItem(),
                'to' => $brokerGroups->lastItem(),
            ],
        ]);
    }

    public function getFormConfig(BrokerGroupForm $formConfig): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $formConfig->getFormData(),
        ]);
    }

    public function store(BrokerGroupStoreRequest $request): JsonResponse
    {
        $brokerGroup = $this->brokerGroupService->createBrokerGroup($request->validated());

        return response()->json([
            'success' => true,
            'data' => new BrokerGroupResource($brokerGroup),
        ]);
    }

    public function update(BrokerGroupStoreRequest $request, int $id): JsonResponse
    {
        $brokerGroup = $this->brokerGroupService->updateBrokerGroup($id, $request->validated());

        return response()->json([
            'success' => true,
            'data' => new BrokerGroupResource($brokerGroup),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $brokerGroup = $this->brokerGroupService->getBrokerGroupDetails($id);

        return response()->json([
            'success' => true,
            'data' => $brokerGroup,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->brokerGroupService->deleteBrokerGroup($id);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Broker group deleted successfully.' : 'Broker group could not be deleted.',
        ]);
    }

    public function searchByBrokerTradingName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trading_name' => ['nullable', 'string', 'max:30'],
        ]);
        $tradingName = $validated['trading_name'] ?? '';
        $brokers = $this->brokerGroupService->searchBrokersByTradingName($tradingName);

        return response()->json([
            'success' => true,
            'data' => $brokers,
        ]);
    }
}
