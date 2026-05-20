<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\PromotionService;
use Modules\Brokers\Http\Requests\IndexPromotionRequest;
use Modules\Brokers\DTOs\PromotionFilters;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Modules\Brokers\Http\Requests\DeletePromotionRequest;
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
        $result = $this->promotionService->getPromotions($filters, $broker_id);

        return ResponseFacade::json($result);
    }

    public function destroy(DeletePromotionRequest $request, int $id, int $broker_id): JsonResponse
    {
        
        $deletedModel = $this->promotionService->deletePromotion($id,$broker_id);

        if (! $deletedModel) {
            return ResponseFacade::json([
                'success' => false,
                'data' => null,
                'message' => 'Promotion not found or could not be deleted',
            ], 404);
        }

        return ResponseFacade::json([
            'success' => true,
            'data' => new PromotionResource($deletedModel),
            'message' => 'Promotion deleted successfully',
        ]);
        
    }
}
