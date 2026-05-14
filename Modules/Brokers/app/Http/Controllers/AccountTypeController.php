<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\DTOs\AccountTypeFilters;
use Modules\Brokers\Http\Requests\DeleteAccountTypeRequest;
use Modules\Brokers\Http\Requests\IndexAccountTypeRequest;
use Modules\Brokers\Services\AccountTypeService;


class AccountTypeController extends Controller
{
    protected AccountTypeService $accountTypeService;

    public function __construct(AccountTypeService $accountTypeService)
    {
        $this->accountTypeService = $accountTypeService;
    }

    public function index(IndexAccountTypeRequest $request, int $broker_id): JsonResponse
    {
            $validatedFilters = $request->validated(); 
            $filters = AccountTypeFilters::from($validatedFilters);
            $result = $this->accountTypeService->getFilteredAccountTypes($filters, $broker_id);
            return response()->json($result, 200);
    }


    public function destroy(DeleteAccountTypeRequest $request, int $id, int $broker_id): JsonResponse
    {
        $accountType = DB::transaction(function () use ($id, $broker_id) {
            $this->accountTypeService->deleteMatrixHeader($id, $broker_id);
            return $this->accountTypeService->deleteAccountType($id, $broker_id);
        });

        return response()->json([
            'success' => true,
            'data' => $accountType,
        ]);
    }
}
