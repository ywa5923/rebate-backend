<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

use Modules\Brokers\Forms\EvaluationForm;
use Modules\Brokers\Http\Requests\StoreEvaluationRuleRequest;
class EvaluationController extends Controller
{
    public function __construct(
        private readonly EvaluationForm $formConfig
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

    /**
     * Store a new evaluation rule
     */
    public function store(StoreEvaluationRuleRequest $request): JsonResponse
    {
        $data = $request->validated();
        dd($data);
        return response()->json([
            'success' => true,
           // 'data' => $this->evaluationRuleService->store($request->all())
        ]);
    }
}
