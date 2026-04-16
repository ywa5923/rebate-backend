<?php

namespace Modules\Brokers\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Brokers\Services\EvaluationStepService;
use Modules\Brokers\Transformers\EvaluationStepResource;
use Modules\Brokers\Http\Requests\EvaluationStepIndexRequest;

class EvaluationStepController extends Controller
{
    public function __construct(
        private readonly EvaluationStepService $evaluationStepService
    ) {}

    public function index(EvaluationStepIndexRequest $request, int $broker_id): JsonResponse
    {
        $data = $request->validated();
        $language = $data['language_code'] ?? 'en';
        $zoneId = $data['zone_id'] ?? null;

        $steps = $this->evaluationStepService->getEvaluationSteps($broker_id, $language, $zoneId);

        return response()->json([
            'success' => true,
            'data' => EvaluationStepResource::collection($steps),
        ]);
    }
}

