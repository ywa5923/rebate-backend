<?php

namespace Modules\Brokers\Http\Controllers;

use App\DTO\ApiData;
use App\DTO\PaginationMeta;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Modules\Brokers\DTOs\PromotionFilters;
use Modules\Brokers\Http\Requests\DeletePromotionRequest;
use Modules\Brokers\Http\Requests\IndexPromotionRequest;
use Modules\Brokers\Services\PromotionService;
use Modules\Brokers\Transformers\PromotionResource;

class PromotionController extends Controller
{
    protected PromotionService $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function index(IndexPromotionRequest $request, int $broker_id): JsonResponse
    {

        $validatedFilters = $request->validated();
        $filters = PromotionFilters::from($validatedFilters);
        $promotions = $this->promotionService->getPromotions($filters, $broker_id);

        return Response::json(ApiData::success(
            data: PromotionResource::collection($promotions)->resolve(),
            pagination: PaginationMeta::fromPaginatorOrNull($promotions),
        ));
    }

    public function destroy(DeletePromotionRequest $request, int $id, int $broker_id): JsonResponse
    {

        $deletedModel = $this->promotionService->deletePromotion($id, $broker_id);

        if (! $deletedModel) {
            return Response::json(ApiData::error(
                message: 'Promotion not found or could not be deleted',
            ), 404);
        }

        return Response::json(ApiData::success(
            data: (new PromotionResource($deletedModel))->resolve(),
            message: 'Promotion deleted successfully',
        ));
    }
}
