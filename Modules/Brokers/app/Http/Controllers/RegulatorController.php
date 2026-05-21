<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Http\Requests\AttachRegulatorToCompanyRequest;
use Modules\Brokers\Http\Requests\DetachRegulatorFromCompanyRequest;
use Modules\Brokers\Http\Requests\GetRegulatorsListRequest;
use Modules\Brokers\Services\RegulatorService;
use Modules\Brokers\Transformers\RegulatorListResource;
use Modules\Brokers\Transformers\RegulatorResource;
use Illuminate\Support\Facades\Response as ResponseFacade;

class RegulatorController extends Controller
{
    public function __construct(
        protected RegulatorService $regulatorService,
    ) {
    }

    public function getRegulatorsList(GetRegulatorsListRequest $request): JsonResponse
    {
        $data = $request->validated();
        $language_code = $data['language_code'];
        $zone_id = $data['zone_id'] ?? null;

        $result = $this->regulatorService->getRegulatorsList($language_code, $zone_id);

        return ResponseFacade::json([
            'success' => true,
            'data' => RegulatorListResource::collection($result),
        ]);
    }

    public function attachRegulatorToCompany(AttachRegulatorToCompanyRequest $request, int $regulator_id, int $company_id, int $broker_id): JsonResponse
    {
        $data = $request->validated();

        $regulator = $this->regulatorService->attachRegulatorToCompany(
            $data['regulator_id'],
            $data['company_id'],
            $data['zone_id'] ?? null,
        );

        return ResponseFacade::json([
            'success' => true,
            'data' => new RegulatorResource($regulator),
        ]);
    }

    public function detachRegulatorFromCompany(DetachRegulatorFromCompanyRequest $request): JsonResponse
    {
        $data = $request->validated();

        $detachedRegulator = $this->regulatorService->detachRegulatorFromCompany(
            $data['regulator_id'],
            $data['company_id'],
            $data['zone_id'] ?? null,
        );

        if(!$detachedRegulator)
        {
            return ResponseFacade::json([
                'success' => false,
                'message' => 'Regulator not found in company',
            ], 404);
        }

        return ResponseFacade::json([
            'success' => true,
            'data' => new RegulatorResource($detachedRegulator),
        ]);
    }
}
