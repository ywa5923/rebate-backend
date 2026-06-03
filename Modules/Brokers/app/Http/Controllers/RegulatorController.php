<?php

namespace Modules\Brokers\Http\Controllers;

use App\DTO\ApiData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Modules\Brokers\Http\Requests\AttachRegulatorToCompanyRequest;
use Modules\Brokers\Http\Requests\DetachRegulatorFromCompanyRequest;
use Modules\Brokers\Http\Requests\GetRegulatorsListRequest;
use Modules\Brokers\Services\RegulatorService;
use Modules\Brokers\Transformers\RegulatorListResource;
use Modules\Brokers\Transformers\RegulatorResource;

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

        $regulators = $this->regulatorService->getRegulatorsList($language_code, $zone_id);

        return Response::json(ApiData::success(
            data: RegulatorListResource::collection($regulators)->resolve(),
        ));
    }

    public function attachRegulatorToCompany(AttachRegulatorToCompanyRequest $request, int $regulator_id, int $company_id, int $broker_id): JsonResponse
    {
        $data = $request->validated();

        $regulator = $this->regulatorService->attachRegulatorToCompany(
            $data['regulator_id'],
            $data['company_id'],
            $data['zone_id'] ?? null,
        );

        return Response::json(ApiData::success(
            data: (new RegulatorResource($regulator))->resolve(),
        ));
    }

    public function detachRegulatorFromCompany(DetachRegulatorFromCompanyRequest $request): JsonResponse
    {
        $data = $request->validated();

        $detachedRegulator = $this->regulatorService->detachRegulatorFromCompany(
            $data['regulator_id'],
            $data['company_id'],
            $data['zone_id'] ?? null,
        );

        if (! $detachedRegulator) {
            return Response::json(ApiData::error(
                message: 'Regulator not found in company',
            ), 404);
        }

        return Response::json(ApiData::success(
            data: (new RegulatorResource($detachedRegulator))->resolve(),
            message: 'Regulator detached from company successfully',
        ));
    }
}
