<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\AccountTypeService;
use Modules\Brokers\Transformers\AccountTypeResource;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Transformers\URLResource;
use Modules\Brokers\Services\UrlService;


class AccountTypeController extends Controller
{
    protected AccountTypeService $accountTypeService;

    public function __construct(AccountTypeService $accountTypeService)
    {
        $this->accountTypeService = $accountTypeService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/account-types",
     *     tags={"AccountType"},
     *     summary="Get all account types",
     *     @OA\Parameter(
     *         name="broker_id",
     *         in="query",
     *         description="Filter by broker ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="zone_id",
     *         in="query",
     *         description="Filter by zone ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="broker_type",
     *         in="query",
     *         description="Filter by broker type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"broker", "crypto", "prop_firm"})
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AcountType")),
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
     * @OA\Get(
     *     path="/api/v1/account-types/create",
     *     tags={"AccountType"},
     *     summary="Get form data for creating account type",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Create form endpoint"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="brokers", type="array", @OA\Items(type="object")), @OA\Property(property="zones", type="array", @OA\Items(type="object")), @OA\Property(property="option_categories", type="array", @OA\Items(type="object")))
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
     * @OA\Post(
     *     path="/api/v1/account-types",
     *     tags={"AccountType"},
     *     summary="Create a new account type",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "broker_type", "broker_id"},
     *             @OA\Property(property="name", type="string", example="Standard Account"),
     *             @OA\Property(property="broker_type", type="string", enum={"broker", "crypto", "prop_firm"}, example="broker"),
     *             @OA\Property(property="commission_value", type="number", format="float", example=1.5),
     *             @OA\Property(property="commission_unit", type="string", example="pips"),
     *             @OA\Property(property="execution_model", type="string", example="STP"),
     *             @OA\Property(property="max_leverage", type="string", example="1:500"),
     *             @OA\Property(property="spread_type", type="string", example="Fixed"),
     *             @OA\Property(property="min_deposit_value", type="string", example="100"),
     *             @OA\Property(property="min_deposit_unit", type="string", example="USD"),
     *             @OA\Property(property="min_trade_size_value", type="string", example="0.01"),
     *             @OA\Property(property="min_trade_size_unit", type="string", example="lots"),
     *             @OA\Property(property="stopout_level_value", type="string", example="20"),
     *             @OA\Property(property="stopout_level_unit", type="string", example="%"),
     *             @OA\Property(property="trailing_stops", type="boolean", example=true),
     *             @OA\Property(property="allow_scalping", type="boolean", example=true),
     *             @OA\Property(property="allow_hedging", type="boolean", example=true),
     *             @OA\Property(property="allow_news_trading", type="boolean", example=true),
     *             @OA\Property(property="allow_cent_accounts", type="boolean", example=false),
     *             @OA\Property(property="allow_islamic_accounts", type="boolean", example=false),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="order", type="integer", example=1),
     *             @OA\Property(property="is_invariant", type="boolean", example=true),
     *             @OA\Property(property="broker_id", type="integer", example=1),
     *             @OA\Property(property="zone_id", type="integer", example=1),
     *             @OA\Property(property="urls", type="array", @OA\Items(
     *                 @OA\Property(property="url", type="string", example="https://example.com"),
     *                 @OA\Property(property="url_type", type="string", example="website"),
     *                 @OA\Property(property="name", type="string", example="Website"),
     *                 @OA\Property(property="slug", type="string", example="website"),
     *                 @OA\Property(property="option_category_id", type="integer", example=1)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Account type created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account type created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AcountType")
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
     * @OA\Get(
     *     path="/api/v1/account-types/{id}",
     *     tags={"AccountType"},
     *     summary="Get account type by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Account type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/AcountType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account type not found"
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
     * @OA\Get(
     *     path="/api/v1/account-types/{id}/urls",
     *     tags={"AccountType"},
     *     summary="Get all URLs for an account type, grouped by url_type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Account type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", additionalProperties={"type": "array", "items": {"type": "object"}})
     *         )
     *     ),
     *     @OA\Response(response=404, description="Account type not found")
     * )
     */
    public function getUrlsGroupedByType($id)
    {
       
        $accountType = AccountType::find($id);
        if (!$accountType) {
            return response()->json([
                'success' => false,
                'message' => 'Account type not found'
            ], 404);
        }

        // Eager load translations for each URL
        $urls = $accountType->urls()->with('translations')->get();

        // Transform each URL using UrlResource
        $transformed = URLResource::collection($urls);

        // Group by url_type
        $grouped = $transformed->groupBy('url_type')->map(function ($items) {
            return $items->values();
        });

        return response()->json([
            'success' => true,
            'data' => $grouped
        ]);
    }

    

    /**
     * @OA\Post(
     *     path="/api/v1/account-types/{id}/urls",
     *     tags={"AccountType"},
     *     summary="Create one or many URLs for an account type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Account type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     example={
     *                         "mobile": {
     *                             { "url": "https://m.example.com", "name": "Mobile", "slug": "mobile" }
     *                         },
     *                         "webplatform": {
     *                             { "url": "https://web.example.com", "name": "Web", "slug": "web" }
     *                         }
     *                     }
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     example={
     *                         "url_type": "mobile",
     *                         "url": "https://m.example.com",
     *                         "name": "Mobile",
     *                         "slug": "mobile"
     *                     }
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(response=201, description="URLs created"),
     *     @OA\Response(response=404, description="Account type not found")
     * )
     */
    // {
    //     "mobile": [
    //       {
    //         "url": "https://m.example.com",
    //         "name": "Mobile",
    //         "slug": "mobile",
    //         "broker_id":1
    //       },
    //       {
    //         "url": "https://m2.example.com",
    //         "name": "Mobile 2",
    //         "slug": "mobile-2",
    //           "broker_id":1
    //       }
    //     ],
    //     "webplatform": [
    //       {
    //         "url": "https://web.example.com",
    //         "name": "Web Platform",
    //         "slug": "webplatform",
    //           "broker_id":1
    //       }
    //     ]
    //   }
    //Ex for 1 url post
    // {
    //     "url_type": "mobile",
    //     "url": "https://m.example.com",
    //     "name": "Mobile",
    //     "slug": "mobile",
    //     "broker_id":1
    //   }
    public function createUrls(Request $request, $id)
    {
        $accountType =AccountType::find($id);
        if (!$accountType) {
            return response()->json(['success' => false, 'message' => 'Account type not found'], 404);
        }

        $data = $request->all();
        $urls = $this->flattenUrlInput($data);

        $created = app(UrlService::class)->createMany($accountType, $urls);

        // Optionally, fetch the created URLs for response
        $fetched = $accountType->urls()->with('translations')->latest('id')->take(count($urls))->get();

        return response()->json([
            'success' => true,
            'data' => \Modules\Brokers\Transformers\URLResource::collection($fetched)
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/account-types/{id}/urls",
     *     tags={"AccountType"},
     *     summary="Update one or many URLs for an account type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Account type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     example={
     *                         "mobile": {
     *                             { "id": 1, "url": "https://m.example.com", "name": "Mobile", "slug": "mobile" }
     *                         }
     *                     }
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     example={
     *                         "id": 1,
     *                         "url_type": "mobile",
     *                         "url": "https://m.example.com",
     *                         "name": "Mobile",
     *                         "slug": "mobile"
     *                     }
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(response=200, description="URLs updated"),
     *     @OA\Response(response=404, description="Account type not found")
     * )
     */
    // {
    //     "mobile": [
    //       {
    //         "id": 728,
    //         "url": "https://m.example.com/updated",
    //         "name": "Mobile Updated",
    //         "slug": "mobile"
    //       }
    //     ],
    //     "webplatform": [
    //       {
    //         "id": 729,
    //         "url": "https://web.example.com/updated",
    //         "name": "Web Updated",
    //         "slug": "web"
    //       }
    //     ]
    //   }
    public function updateUrls(Request $request, $id)
    {
        $accountType = \Modules\Brokers\Models\AccountType::find($id);
        if (!$accountType) {
            return response()->json(['success' => false, 'message' => 'Account type not found'], 404);
        }

        $data = $request->all();
        $urls = $this->flattenUrlInput($data);

        $updated = app(UrlService::class)->updateMany($accountType, $urls);

        return response()->json([
            'success' => true,
            'data' => \Modules\Brokers\Transformers\URLResource::collection(collect($updated))
        ]);
    }

    /**
     * Helper to flatten grouped or single URL input.
     *
     * This function normalizes the input so that whether the client sends:
     *
     * 1. Grouped by url_type:
     *    {
     *      "mobile": [
     *        { "url": "https://m.example.com", "name": "Mobile", "slug": "mobile" }
     *      ],
     *      "webplatform": [
     *        { "url": "https://web.example.com", "name": "Web", "slug": "web" }
     *      ]
     *    }
     *
     * 2. Or a single URL:
     *    {
     *      "url_type": "mobile",
     *      "url": "https://m.example.com",
     *      "name": "Mobile",
     *      "slug": "mobile"
     *    }
     *
     * It always returns a flat array of URLs like:
     *    [
     *      [ 'url_type' => 'mobile', 'url' => '...', ... ],
     *      [ 'url_type' => 'webplatform', 'url' => '...', ... ],
     *      ...
     *    ]
     *
     * @param array $data
     * @return array
     */
    private function flattenUrlInput($data)
    {
        $urls = [];
        // If input is a single URL (has url_type), wrap it in an array
        if (isset($data['url_type'])) {
            $urls[] = $data;
        } else {
            // If input is grouped by url_type, flatten it
            foreach ($data as $type => $urlArr) {
                foreach ($urlArr as $url) {
                    $url['url_type'] = $type;
                    $urls[] = $url;
                }
            }
        }
        return $urls;
    }

    /**
     * @OA\Put(
     *     path="/api/v1/account-types/{id}",
     *     tags={"AccountType"},
     *     summary="Update account type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Account type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Standard Account"),
     *             @OA\Property(property="broker_type", type="string", enum={"broker", "crypto", "prop_firm"}, example="broker"),
     *             @OA\Property(property="commission_value", type="number", format="float", example=2.0),
     *             @OA\Property(property="commission_unit", type="string", example="pips"),
     *             @OA\Property(property="execution_model", type="string", example="ECN"),
     *             @OA\Property(property="max_leverage", type="string", example="1:1000"),
     *             @OA\Property(property="spread_type", type="string", example="Variable"),
     *             @OA\Property(property="min_deposit_value", type="string", example="200"),
     *             @OA\Property(property="min_deposit_unit", type="string", example="USD"),
     *             @OA\Property(property="min_trade_size_value", type="string", example="0.01"),
     *             @OA\Property(property="min_trade_size_unit", type="string", example="lots"),
     *             @OA\Property(property="stopout_level_value", type="string", example="25"),
     *             @OA\Property(property="stopout_level_unit", type="string", example="%"),
     *             @OA\Property(property="trailing_stops", type="boolean", example=true),
     *             @OA\Property(property="allow_scalping", type="boolean", example=true),
     *             @OA\Property(property="allow_hedging", type="boolean", example=true),
     *             @OA\Property(property="allow_news_trading", type="boolean", example=true),
     *             @OA\Property(property="allow_cent_accounts", type="boolean", example=false),
     *             @OA\Property(property="allow_islamic_accounts", type="boolean", example=false),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="order", type="integer", example=1),
     *             @OA\Property(property="is_invariant", type="boolean", example=true),
     *             @OA\Property(property="broker_id", type="integer", example=1),
     *             @OA\Property(property="zone_id", type="integer", example=1),
     *             @OA\Property(property="urls", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="url", type="string", example="https://example.com"),
     *                 @OA\Property(property="url_type", type="string", example="website"),
     *                 @OA\Property(property="name", type="string", example="Website"),
     *                 @OA\Property(property="slug", type="string", example="website"),
     *                 @OA\Property(property="option_category_id", type="integer", example=1)
     *             )),
     *             @OA\Property(property="urls_to_delete", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account type updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account type updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AcountType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account type not found"
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
     * @OA\Delete(
     *     path="/api/v1/account-types/{id}",
     *     tags={"AccountType"},
     *     summary="Delete account type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Account type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account type deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account type deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account type not found"
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

    /**
     * @OA\Delete(
     *     path="/api/v1/account-types/{accountTypeId}/urls/{urlId}",
     *     tags={"AccountType"},
     *     summary="Delete a single URL for an account type",
     *     @OA\Parameter(
     *         name="accountTypeId",
     *         in="path",
     *         description="Account type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="urlId",
     *         in="path",
     *         description="URL ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="URL deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="URL deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="URL or Account type not found")
     * )
     */
    public function deleteUrl($accountTypeId, $urlId)
    {
        $accountType = AccountType::find($accountTypeId);
        if (!$accountType) {
            return response()->json(['success' => false, 'message' => 'Account type not found'], 404);
        }
        $url = $accountType->urls()->find($urlId);
        if (!$url) {
            return response()->json(['success' => false, 'message' => 'URL not found'], 404);
        }
        $url->delete();
        return response()->json([
            'success' => true,
            'message' => 'URL deleted successfully'
        ]);
    }
}
