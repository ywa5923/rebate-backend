<?php

namespace Modules\Brokers\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Modules\Brokers\Http\Requests\IndexEvaluationStepRequest;
use Modules\Brokers\Services\EvaluationStepService;
use Modules\Brokers\Transformers\EvaluationStepResource;
use Modules\Brokers\Http\Requests\DeleteEvaluationStepRequest;
class EvaluationStepController extends Controller
{
    public function __construct(
        private readonly EvaluationStepService $evaluationStepService,
    ) {
    }

    public function index(
        IndexEvaluationStepRequest $request,
        int $broker_id,
    ): JsonResponse {
        $data = $request->validated();
        $language = $data['language_code'];
        $zoneId = $data['zone_id'];

        $steps = $this->evaluationStepService->getEvaluationSteps(
            $broker_id,
            $language,
            $zoneId,
        );

        return ResponseFacade::json([
            'success' => true,
            'data' => EvaluationStepResource::collection($steps),
        ]);
    }

    public function destroy(
        DeleteEvaluationStepRequest $request,
        int $id,
        int $broker_id,
    ): JsonResponse {
        $deletedModel = $this->evaluationStepService->deleteEvaluationStep($id, $broker_id);
        if (! $deletedModel) {
            return ResponseFacade::json([
                'success' => false,
                'data' => null,
                'message' => 'Evaluation step not found or could not be deleted',
            ], 404);
        }
    
        return ResponseFacade::json([
            'success' => true,
            'data' => new EvaluationStepResource($deletedModel),
            'message' => 'Evaluation step deleted successfully',
        ]);
    }
}
