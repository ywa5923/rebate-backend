<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\CompanyService;
use Modules\Brokers\Transformers\CompanyResource;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/companies",
     *     tags={"Company"},
     *     summary="Get all companies",
     *     @OA\Parameter(
     *         name="broker_id",
     *         in="query",
     *         description="Filter by broker ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"published", "pending", "rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in name and licence_number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "status", "year_founded", "created_at", "updated_at"})
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="language_code",
     *         in="query",
     *         description="Language code for translations",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Company")),
     *             @OA\Property(property="pagination", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="total", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            //get companies by query params: broker_id, status, search, sort_by, sort_direction, per_page, language_code
            $result = $this->companyService->getCompanies($request);
          
            // Transform the data collection
            $result['data'] = CompanyResource::collection($result['data']);
            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve companies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/companies/create",
     *     tags={"Company"},
     *     summary="Get form data for creating company",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Create form endpoint"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="brokers", type="array", @OA\Items(type="object")), @OA\Property(property="zones", type="array", @OA\Items(type="object")), @OA\Property(property="status_options", type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function create(): JsonResponse
    {
        try {
            $formData = $this->companyService->getFormData();
            
            return response()->json([
                'success' => true,
                'message' => 'Create form endpoint',
                'data' => $formData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get form data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/companies",
     *     tags={"Company"},
     *     summary="Create a new company",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="TechCorp Solutions", maxLength=250),
     *             @OA\Property(property="name_p", type="string", example="TechCorp Solutions P", maxLength=250),
     *             @OA\Property(property="licence_number", type="string", example="TECH-2024-001"),
     *             @OA\Property(property="licence_number_p", type="string", example="TECH-2024-001-P", maxLength=250),
     *             @OA\Property(property="banner", type="string", example="https://example.com/banners/techcorp-banner.jpg"),
     *             @OA\Property(property="banner_p", type="string", example="https://example.com/banners/techcorp-banner-p.jpg"),
     *             @OA\Property(property="description", type="string", example="Leading technology solutions provider"),
     *             @OA\Property(property="description_p", type="string", example="Leading technology solutions provider"),
     *             @OA\Property(property="year_founded", type="string", example="2018"),
     *             @OA\Property(property="year_founded_p", type="string", example="2018"),
     *             @OA\Property(property="employees", type="string", example="250-500"),
     *             @OA\Property(property="employees_p", type="string", example="250-500"),
     *             @OA\Property(property="headquarters", type="string", example="San Francisco, California, USA", maxLength=1000),
     *             @OA\Property(property="headquarters_p", type="string", example="San Francisco, California, USA", maxLength=1000),
     *             @OA\Property(property="offices", type="string", example="New York, London, Singapore, Tokyo", maxLength=1000),
     *             @OA\Property(property="offices_p", type="string", example="New York, London, Singapore, Tokyo", maxLength=1000),
     *             @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}, example="published"),
     *             @OA\Property(property="status_reason", type="string", example="", maxLength=1000),
     *             @OA\Property(property="broker_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
     *             @OA\Property(property="zone_code", type="string", example="US", maxLength=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Company created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Company created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Company")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate data
            $validatedData = $this->companyService->validateCompanyData($request->all());
            
            // Create company
            $company = $this->companyService->createCompany($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'data' => $company,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/companies/{id}",
     *     tags={"Company"},
     *     summary="Get company by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Company ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Company")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $company = $this->companyService->getCompanyById($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $company
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve company',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * @OA\Put(
     *     path="/api/v1/companies/{id}",
     *     tags={"Company"},
     *     summary="Update company",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Company ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated TechCorp Solutions", maxLength=250),
     *             @OA\Property(property="name_p", type="string", example="Updated TechCorp Solutions P", maxLength=250),
     *             @OA\Property(property="licence_number", type="string", example="TECH-2024-001-REV"),
     *             @OA\Property(property="licence_number_p", type="string", example="TECH-2024-001-REV-P", maxLength=250),
     *             @OA\Property(property="banner", type="string", example="https://example.com/banners/techcorp-banner-new.jpg"),
     *             @OA\Property(property="banner_p", type="string", example="https://example.com/banners/techcorp-banner-new-p.jpg"),
     *             @OA\Property(property="description", type="string", example="Updated: Leading technology solutions provider"),
     *             @OA\Property(property="description_p", type="string", example="Updated: Leading technology solutions provider"),
     *             @OA\Property(property="year_founded", type="string", example="2018"),
     *             @OA\Property(property="year_founded_p", type="string", example="2018"),
     *             @OA\Property(property="employees", type="string", example="500-750"),
     *             @OA\Property(property="employees_p", type="string", example="500-750"),
     *             @OA\Property(property="headquarters", type="string", example="San Francisco, California, USA", maxLength=1000),
     *             @OA\Property(property="headquarters_p", type="string", example="San Francisco, California, USA", maxLength=1000),
     *             @OA\Property(property="offices", type="string", example="New York, London, Singapore, Tokyo, Berlin, Toronto", maxLength=1000),
     *             @OA\Property(property="offices_p", type="string", example="New York, London, Singapore, Tokyo, Berlin, Toronto", maxLength=1000),
     *             @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}, example="published"),
     *             @OA\Property(property="status_reason", type="string", example="", maxLength=1000),
     *             @OA\Property(property="broker_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3, 4, 5}),
     *             @OA\Property(property="zone_code", type="string", example="US", maxLength=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Company updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Company")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Validate data
            $validatedData = $this->companyService->validateCompanyData($request->all(), true);
            
            // Update company
            $company = $this->companyService->updateCompany($id, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully',
                'data' => $company
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/companies/{id}",
     *     tags={"Company"},
     *     summary="Delete company",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Company ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Company deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->companyService->deleteCompany($id);

            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete company',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
