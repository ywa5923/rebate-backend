<?php

namespace Modules\Brokers\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

use Modules\Brokers\Services\CompanyService;
use Modules\Brokers\Transformers\CompanyResource;
use Modules\Brokers\Transformers\EvaluationStepResource;
use Modules\Brokers\Services\EvaluationStepService;
use Modules\Brokers\Http\Requests\DynamicTableIndexRequest;

class DynamicTableController extends Controller
{
    /**
     * Version 3: config-driven handler map
     */
    private array $handlers = [
        'company' => [
            'service'  => \Modules\Brokers\Services\CompanyService::class,
            'method'   => 'getCompanies',
            'resource' => \Modules\Brokers\Transformers\CompanyResource::class,
            'args'     => ['request','broker_id'],
        ],
        'evaluation-steps' => [
            'service'  => \Modules\Brokers\Services\EvaluationStepService::class,
            'method'   => 'getEvaluationSteps',
            'resource' => \Modules\Brokers\Transformers\EvaluationStepResource::class,
            'args'     => ['broker_id','language','zone'],
        ],
    ];

    public function __construct(
       
    ){}
    public function index(DynamicTableIndexRequest $request,int $broker_id, string $model): JsonResponse
    {

        
        $handler = $this->handlers[$model] ?? null;
        if (!$handler) {
            return response()->json([
                'success' => false,
                'message' => 'Model not found'
            ], 404);
        }

        $service = app($handler['service']);
        $callArgs = [];
        foreach ($handler['args'] as $arg) {
            $callArgs[] = match ($arg) {
                'request' => $request,
                'broker_id' => $broker_id,
                'language' => $request->language_code,
                'zone' => $request->zone_code,
                default => null,
            };
        }
        $result = $service->{$handler['method']}(...$callArgs);
        // unwrap if service returns ['data'=>...]
        $collectionInput = (is_array($result) && array_key_exists('data', $result)) ? $result['data'] : $result;
        $resourceClass = $handler['resource'];

        return response()->json([
            'success' => true,
            'data' => $resourceClass::collection($collectionInput),
        ], 200);

       
    }
}