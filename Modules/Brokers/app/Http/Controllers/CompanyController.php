<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Http\Requests\DeleteCompanyRequest;
use Modules\Brokers\Http\Requests\GetCompanyRegulatorsRequest;
use Modules\Brokers\Http\Requests\IndexCompanyRequest;
use Modules\Brokers\Services\CompanyService;
use Modules\Brokers\Transformers\CompanyResource;
use Modules\Brokers\Transformers\RegulatorResource;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index(IndexCompanyRequest $request, int $broker_id): JsonResponse
    {
        $data = $request->validated();
        $language_code = $data['language_code']; //default to en if not provided
        $zone_id = $data['zone_id'] ?? null;

        $result = $this->companyService->getCompanies($broker_id, $language_code, $zone_id);

        return response()->json([
            'success' => true,
            'data' => CompanyResource::collection($result),
        ], 200);
    }

    public function destroy(DeleteCompanyRequest $request, int $id, int $broker_id): JsonResponse
    {
        $this->companyService->deleteCompany($id);

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully',
        ]);
    }

    public function getRegulators(GetCompanyRegulatorsRequest $request, int $company_id, int $broker_id): JsonResponse
    {
        $data = $request->validated();
        $language_code = $data['language_code'];
        $zone_id = $data['zone_id'] ?? null;

        $result = $this->companyService->getRegulators($data['company_id'], $language_code, $zone_id);

        return response()->json([
            'success' => true,
            'data' => RegulatorResource::collection($result),
        ], 200);
    }
}
