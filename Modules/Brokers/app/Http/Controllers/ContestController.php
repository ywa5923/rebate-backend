<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\DTOs\ContestFilters;
use Modules\Brokers\Http\Requests\DeleteContestRequest;
use Modules\Brokers\Http\Requests\IndexContestRequest;
use Modules\Brokers\Services\ContestService;
use Modules\Brokers\Transformers\ContestResource;
use Illuminate\Support\Facades\Response as ResponseFacade;

class ContestController extends Controller
{
    protected ContestService $contestService;

    public function __construct(ContestService $contestService)
    {
        $this->contestService = $contestService;
    }

    public function index(IndexContestRequest $request, int $broker_id): JsonResponse
    {
        $validatedFilters = $request->validated();
        $filters = ContestFilters::from($validatedFilters);
        $result = $this->contestService->getContests($filters, $broker_id);

        return ResponseFacade::json($result);
    }

    public function destroy(DeleteContestRequest $request, int $id,int $broker_id): JsonResponse
    {
        
        $deletedModel = $this->contestService->deleteContest($id, $broker_id);

        if (! $deletedModel) {
            return ResponseFacade::json([
                'success' => false,
                'data' => null,
                'message' => 'Contest not found or could not be deleted',
            ], 404);
        }

        return ResponseFacade::json([
            'success' => true,
            'data' => new ContestResource($deletedModel),
            'message' => 'Contest deleted successfully',
        ]);
    }
}
