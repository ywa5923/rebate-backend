<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Services\AccountTypeService;
use Modules\Brokers\Transformers\AccountTypeResource;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Transformers\URLResource;
use Modules\Brokers\Services\UrlService;
use App\Utilities\ModelHelper;


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
    public function createUrls(Request $request, $id=null)
    {
        // TO DO verify that the logged in broker id is the same as the broker_id in the request
        //or is admin
        $broker_id = $request->broker_id;
        $isAdmin=false;
        $id = ($id === 'null' || $id === '') ? null : $id;
        if ($broker_id == null) {
            throw new \Exception('Broker ID is required');
        }


        //if urlable_id is  null which is the id for AccountType, it means that is a master url that has urlable_id null 
        //it is avaialble for all broker account types
        if ($id) {
            //if account type id is not null, it means that is a broker account type
            $accountType = AccountType::find($id);

            if (!$accountType) {
                return response()->json(['success' => false, 'message' => 'Account type not found'], 404);
            }
        } else {
            $accountType = null;
        }


        $data = $request->all();

        $urls = $this->flattenUrlInput($data);


        $created = app(UrlService::class)->createMany($accountType, 'account_type', $urls,$isAdmin);


        // Optionally, fetch the created URLs for response
        if ($accountType) {
            $fetched = $accountType->urls()->latest('id')->take(count($urls))->get();
        } else {
            $fetched = Url::where('urlable_type', AccountType::class)->where('broker_id', $broker_id)->latest('id')->take(count($urls))->get();
        }


        return response()->json([
            'success' => true,
            // 'data' => \Modules\Brokers\Transformers\URLResource::collection($fetched)
            'data' => $fetched
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
    public function updateUrls(Request $request, $id=null)
    {
        // Convert string "null" to actual null
        $id = ($id === 'null' || $id === '') ? null : $id;

        // TO DO verify that the logged in broker id is the same as the broker_id in the request
        //or is admin
        $broker_id = $request->broker_id;
        
        $isAdmin=false;
        if ($broker_id == null) {
            throw new \Exception('Broker ID is required as a search parameter');
        }

       // dd($id);
        if ($id) {
            //if account type id is not null, it means that is a broker account type
            $accountType = AccountType::find($id);

            if (!$accountType) {
                return response()->json(['success' => false, 'message' => 'Account type not found'], 404);
            }
        } else {
            $accountType = null;
        }

        $data = $request->all();
        $urls = $this->flattenUrlInput($data);

        

        $updated = app(UrlService::class)->updateMany('account_type', $urls, $broker_id,$isAdmin);

        return response()->json([
            'success' => true,
            'data' => URLResource::collection(collect($updated))
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
    public function destroy(Request $request, $id): JsonResponse
    {

        // TODO: Check if account type broker id is the same as the logged in broker id or is admin
        $brokerId = $request->input('broker_id');

        if (!$brokerId) {
            return response()->json([
                'success' => false,
                'message' => 'Broker ID is required as a search parameter'
            ], 400);
        }

        try {

            DB::transaction(function () use ($id, $brokerId) {
                $this->accountTypeService->deleteMatrixHeader($id, $brokerId);
                $this->accountTypeService->deleteAccountType($id, $brokerId);
            });

            return response()->json([
                'success' => true,
                'message' => 'Account type deleted successfully'
            ]);
        } catch (\Throwable $e) {
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
    public function deleteAccountTypeUrl($accountTypeId, $urlId)
    {

        // TO DO
        //check if account type broker id is the same as the logged in broker id
        //or is admin

        // $brokerId = $request->input('broker_id');
        // if (!$brokerId) {
        //     return response()->json(['success' => false, 'message' => 'Broker ID required'], 400);
        // }

        // // TODO: Verify broker owns this account type
        // $accountType = AccountType::where('id', $accountTypeId)
        //     ->where('broker_id', $brokerId)
        //     ->first();

        $accountType = AccountType::find($accountTypeId);

        if (!$accountType) {
            return response()->json(['success' => false, 'message' => 'Account type not found'], 404);
        }

        $allUrls = $accountType->getAllAccountTypeUrls();
        $url = $allUrls->firstWhere('id', $urlId);
        if (!$url) {
            return response()->json(['success' => false, 'message' => 'URL not found'], 404);
        }

        try {
            $url->delete();

            return response()->json([
                'success' => true,
                'message' => 'URL deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete URL',
                'error' => $e->getMessage()  // Add this for debugging
            ], 500);
        }
    }
}
