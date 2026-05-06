<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Brokers\DTOs\StoreAffiliateLinkDTO;
use Modules\Brokers\Enums\UrlTypeEnum;
use Modules\Brokers\Http\Requests\IndexAffiliateLinkRequest;
use Modules\Brokers\Http\Requests\StoreAffiliateLinkRequest;
use Modules\Brokers\Services\DropdownListService;
use Modules\Brokers\Services\UrlService;
use Modules\Brokers\Transformers\URLResource;
use Symfony\Component\HttpFoundation\JsonResponse;

class UrlController extends Controller
{
    protected UrlService $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    public function getGroupedUrls(int $broker_id, string $entity_type, int|string $entity_id, Request $request)
    {
        //http://localhost:8080/api/v1/urls/2/account-type/1?zone_code=eu&language_code=en
        //go get urls for all account types $entity_id is "all"
        //ex for all http://localhost:8080/api/v1/urls/2/account-type/all?zone_code=eu&language_code=en

        $this->urlService->validateData($broker_id, $entity_type, (int)$entity_id, $request);
        $zone_code = $request->query('zone_code') ?? null;
        $language_code = $request->query('language_code') ?? 'en';

        $urls = $this->urlService->getUrlsByEntity($broker_id, $entity_type, (int)$entity_id, $zone_code, $language_code);
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
        $storeAffiliateLinkDto = StoreAffiliateLinkDTO::fromValidated($data);
        //$isAdmin = app('isAdmin');

        $isAdmin = true;
        $zone_id = $request->validated('zone_id');
       
        $url = $this->urlService->createAffiliateLink($storeAffiliateLinkDto, $broker_id, $isAdmin);

        return response()->json([
            'success' => true,
            'data' => $url,
        ]);
    }

    public function updateBrokerAffiliateLink(StoreAffiliateLinkRequest $request, int $broker_id, int $url_id)
    {
        //$isAdmin = app('isAdmin');
        $isAdmin = true;
        $data = $request->validated();
        $updateAffiliateLinkDto = StoreAffiliateLinkDTO::fromValidated($data);
        //$url = $this->urlService->updateBrokerAffiliateLink($broker_id, $url_id, $data, $isAdmin);
        $url = $this->urlService->updateAffiliateLink($updateAffiliateLinkDto, $url_id, $broker_id, $isAdmin);

        return response()->json([
            'success' => true,
            'data' => $url,
        ]);
    }

    public function deleteBrokerAffiliateLink(Request $request, int $broker_id, int $url_id)
    {
        $brokerId = (int) $request->validate([
            'broker_id' => ['required', 'integer'],
        ])['broker_id'];

        $urlId = (int) $request->validate([
            'url_id' => ['required', 'integer'],
        ])['url_id'];

        $url = $this->urlService->deleteAffiliateLink($brokerId, $urlId);

        return response()->json([
            'success' => true,
            'data' => $url,
        ]);
    }

    /**
     * Broker affiliate links (IB / sub-IB) plus account types with merged platform URLs and currency dropdown options.
     *
     * @phpstan-return JsonResponse<array{
     *     success: bool,
     *     data: array{
     *         account_types: \Modules\Brokers\Transformers\AccountTypeUrlsCollection,
     *         ib_affiliate_urls: \Modules\Brokers\Transformers\AffiliateLinkCollection,
     *         sub_ib_affiliate_urls: \Modules\Brokers\Transformers\AffiliateLinkCollection,
     *         currency_list: list<array{label: string, value: string}>
     *     }
     * }>
     */
    public function getBrokerAffiliateLinks(IndexAffiliateLinkRequest $request, DropdownListService $dropdownListService, int $broker_id): JsonResponse
    {
        $lang = $request->validated('language_code');
        $zone = $request->validated('zone_code');
        $accountTypes = $this->urlService->getAccountTypesWithPlatformLinks($broker_id, $lang, $zone);

        $currencyList = $dropdownListService->getCurrencyListOptions(); // Assuming 1 is the ID for currency list
        $affliateLinksDTO = $this->urlService->getAffiliateLinks($broker_id, $lang, $zone);

        return response()->json([
            'success' => true,
            'data' => [
                'account_types' => $accountTypes,
                'ib_affiliate_urls' => $affliateLinksDTO->ibAffiliateUrls,
                'sub_ib_affiliate_urls' => $affliateLinksDTO->subIbAffiliateUrls,
                'currency_list' => $currencyList,
            ],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id)
    {
       
    }
}
