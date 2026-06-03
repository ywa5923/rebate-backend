<?php

namespace Modules\Brokers\Http\Controllers;

use App\DTO\ApiData;
use App\DTO\PaginationMeta;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Modules\Brokers\DTOs\ContestFilters;
use Modules\Brokers\Http\Requests\DeleteContestRequest;
use Modules\Brokers\Http\Requests\IndexContestRequest;
use Modules\Brokers\Services\ContestService;
use Modules\Brokers\Transformers\ContestResource;

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
        $contests = $this->contestService->getContests($filters, $broker_id);

        return Response::json(ApiData::success(
            data: ContestResource::collection($contests)->resolve(),
            pagination: PaginationMeta::fromPaginatorOrNull($contests),
        ));
    }

    public function destroy(DeleteContestRequest $request, int $id, int $broker_id): JsonResponse
    {

        $deletedModel = $this->contestService->deleteContest($id, $broker_id);

        if (! $deletedModel) {
            return Response::json(ApiData::error(
                message: 'Contest not found or could not be deleted',
            ), 404);
        }

        return Response::json(ApiData::success(
            data: (new ContestResource($deletedModel))->resolve(),
            message: 'Contest deleted successfully',
        ));
    }
}
