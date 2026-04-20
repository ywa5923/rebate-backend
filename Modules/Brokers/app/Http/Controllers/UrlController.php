<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Brokers\Enums\UrlTypeEnum;
use Modules\Brokers\Http\Requests\IndexAffiliateLinkRequest;
use Modules\Brokers\Http\Requests\StoreAffiliateLinkRequest;
use Modules\Brokers\Http\Requests\UpdateAffiliateLinkRequest;
use Modules\Brokers\Services\UrlService;
use Modules\Brokers\Transformers\URLResource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Modules\Brokers\Services\DropdownListService;

class UrlController extends Controller
{
    protected UrlService $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    public function getGroupedUrls($broker_id, $entity_type, $entity_id, Request $request)
    {
        //http://localhost:8080/api/v1/urls/2/account-type/1?zone_code=eu&language_code=en
        //go get urls for all account types $entity_id is "all"
        //ex for all http://localhost:8080/api/v1/urls/2/account-type/all?zone_code=eu&language_code=en

        $this->urlService->validateData($broker_id, $entity_type, $entity_id, $request);
        $zone_code = $request->query('zone_code') ?? null;
        $language_code = $request->query('language_code') ?? 'en';

        $urls = $this->urlService->getUrlsByEntity($broker_id, $entity_type, $entity_id, $zone_code, $language_code);
        $transformed = URLResource::collection($urls);

        $grouped = $transformed->groupBy([
            function ($item) {
                return $item['urlable_id'] ? $item['urlable_id'] : 'master-links';
            },
            function ($item) {
                return $item['url_type'];
            },
        ]);
        $masterLinks = $grouped['master-links'] ?? [];
        unset($grouped['master-links']);

        return response()->json([
            'success' => true,
            'data' => [
                'links_grouped_by_account_id' => $grouped,
                'master_links_grouped_by_type' => $masterLinks,
                'links_groups' => [UrlTypeEnum::MOBILE->value, UrlTypeEnum::WEBPLATFORM->value, UrlTypeEnum::SWAP->value, UrlTypeEnum::COMMISSION->value],
                //'links_groups' => ['mobile', 'webplatform', 'swap', 'commission']
            ],

        ]);
    }

    // //To do
    // public function getAccountTypeAffiliateLinks( Request $request,int $account_type_id,int $broker_id)
    // {
    //   $accountType = AccountType::find($account_type_id);
    //   if (!$accountType) {
    //     return response()->json([
    //       'success' => false,
    //       'message' => 'Account type not found'
    //     ], 404);
    //   }
    //   $urls = $accountType->urls()->where('url_type', 'affiliate1')->orWhere('url_type', 'affiliate2')->orWhere('url_type', 'affiliate3')->with('translations')->get();
    //   $transformed = URLResource::collection($urls);
    //   return response()->json([
    //     'success' => true,
    //     'data' => $transformed
    //   ]);
    // }

    //to check if this works
    public function createBrokerAffiliateLink(StoreAffiliateLinkRequest $request, int $broker_id)
    {

        $data = $request->validated();
        //$isAdmin = app('isAdmin');
        $isAdmin = false;
        $url = $this->urlService->createBrokerAffiliateLink($broker_id, $data, $isAdmin);

        return response()->json([
            'success' => true,
            'data' => $url,
        ]);
    }

    public function updateBrokerAffiliateLink(UpdateAffiliateLinkRequest $request, int $broker_id, $url_id)
    {
        //$isAdmin = app('isAdmin');
        $isAdmin = false;
        $data = $request->validated();

        $url = $this->urlService->updateBrokerAffiliateLink($broker_id, $url_id, $data, $isAdmin);

        return response()->json([
            'success' => true,
            'data' => $url,
        ]);
    }

    public function deleteBrokerAffiliateLink(int $broker_id, int $url_id)
    {
        $url = $this->urlService->deleteBrokerAffiliateLink($broker_id, $url_id);

        return response()->json([
            'success' => true,
            'data' => $url,
        ]);
    }

    /**
     * Get all affiliate links for a given broker
     *
     * @return JsonResponse<array{
     *     "success": bool,
     *     "data": array{
     *         "account_types": array<AccountType>,
     *         "ib_affiliate_urls"?: array<URL>,
     *         "sub_ib_affiliate_urls"?: array<URL>
     *        "currency_list": array<array{label: string, value: string}>
     *     }
     * }>
     */
    public function getBrokerAffiliateLinks(IndexAffiliateLinkRequest $request, DropdownListService $dropdownListService, int $broker_id): JsonResponse
    {
        $lang = $request->validated('language_code');
        $zone = $request->validated('zone_code');

        $currencyList = $dropdownListService->getCurrencyListOptions(); // Assuming 1 is the ID for currency list

       
        return response()->json([
            'success' => true,
            'data' => array_merge($this->urlService->getBrokerAffiliateLinks($broker_id, $lang, $zone), ['currency_list' => $currencyList]),

        ], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
