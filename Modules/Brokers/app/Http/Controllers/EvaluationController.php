<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Forms\EvaluationForm;
use Modules\Brokers\Http\Requests\StoreEvaluationRuleRequest;
use Modules\Brokers\Http\Requests\EvaluationIndexRequest;
use Modules\Brokers\Services\EvaluationRuleService;
use Modules\Brokers\Transformers\BrokerEvaluationResource;
use Modules\Brokers\Models\BrokerEvaluation;


class EvaluationController extends Controller
{
    public function __construct(
        private readonly EvaluationForm $formConfig,
        private readonly EvaluationRuleService $evaluationRuleService
    ){}
    

    /**
     * Get the form configuration for the evaluation rules
     */
    public function getFormConfig(): JsonResponse
    {
      
        return response()->json([
            'success' => true,
            'data' => $this->formConfig->getFormData()
        ]);
    }

    public function index(EvaluationIndexRequest $request, int $broker_id): JsonResponse
    {
        $data = $request->validated();
        $zone_id = $data['zone_id'] ?? null;
        $lang = $data['lang'] ?? 'en';
        $evaluations = $this->evaluationRuleService->getEvaluations($broker_id,$lang, $zone_id);
        return response()->json([
            'success' => true,
            'data' => BrokerEvaluationResource::collection($evaluations)
        ]);
    }

    /**
     * Store a new evaluation rule
     */
    public function storeOrUpdate(StoreEvaluationRuleRequest $request,$broker_id): JsonResponse
    {
        $is_admin=false;
        $data = $request->validated();
        $this->evaluationRuleService->upsertEvaluationRule($data, $broker_id, $is_admin);
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Delete a broker evaluation by id for the given broker.
     */
    public function destroy(int $broker_id, int $id): JsonResponse
    {
        $deleted = $this->evaluationRuleService->deleteEvaluation($broker_id, $id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Evaluation not found',
            ], 404);
        }

        return response()->json(['success' => true]);
    }
}
