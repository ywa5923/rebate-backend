<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\AccountTypeService;
use Modules\Brokers\Transformers\AccountTypeResource;

class AcountTypeController extends Controller
{
    protected AccountTypeService $accountTypeService;

    public function __construct(AccountTypeService $accountTypeService)
    {
        $this->accountTypeService = $accountTypeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            //get ac types by query params: broker_id, zone_id, broker_type, sort_by, sort_direction, per_page,language_code
            //
            $result = $this->accountTypeService->getAccountTypes($request);
          
            // Transform the data collection
            $result['data'] = AccountTypeResource::collection($result['data']);
            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve account types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): JsonResponse
    {
        try {
            $formData = $this->accountTypeService->getFormData();
            
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate data
            $validatedData = $this->accountTypeService->validateAccountTypeData($request->all());
            
           // dd($validatedData);
            // Create account type
            $accountType = $this->accountTypeService->createAccountType($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Account type created successfully',
                'data' => $accountType,
                //'data' => new AccountTypeResource($accountType)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create account type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $accountType = $this->accountTypeService->getAccountTypeById($id);

            if (!$accountType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account type not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $accountType
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve account type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
       
        try {
            // Validate data
            $validatedData = $this->accountTypeService->validateAccountTypeData($request->all(), true);
           // dd($request->all());
            // Update account type
            $accountType = $this->accountTypeService->updateAccountType($id, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Account type updated successfully',
                'data' => $accountType
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->accountTypeService->deleteAccountType($id);

            return response()->json([
                'success' => true,
                'message' => 'Account type deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account type',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
