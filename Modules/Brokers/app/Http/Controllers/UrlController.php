<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Brokers\DTOs\AccountTypeUrlDTO;
use Modules\Brokers\DTOs\StoreAffiliateLinkDTO;
use Modules\Brokers\Enums\UrlTypeEnum;
use Modules\Brokers\Http\Requests\GetGroupedUrlsRequest;
use Modules\Brokers\Http\Requests\IndexAffiliateLinkRequest;
use Modules\Brokers\Http\Requests\StoreAccountTypeUrlRequest;
use Modules\Brokers\Http\Requests\StoreAffiliateLinkRequest;
use Modules\Brokers\Services\DropdownListService;
use Modules\Brokers\Services\UrlService;
use Symfony\Component\HttpFoundation\JsonResponse;

class UrlController extends Controller
{
    protected UrlService $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    public function getGroupedUrls(GetGroupedUrlsRequest $request, int $broker_id, string $entity_type, int|string $entity_id)
    {
        //http://localhost:8080/api/v1/urls/2/account-type/1?zone_code=eu&language_code=en
        //go get urls for all account types $entity_id is "all"
        //ex for all http://localhost:8080/api/v1/urls/2/account-type/all?zone_code=eu&language_code=en

        $entityId = $request->validated('entity_id');
        //for master links, entity_id is 'all' so it is converted to null
        $groupedUrlsDTO = $this->urlService->getGroupedUrlsByEntity(
            $request->validated('broker_id'),
            $request->validated('entity_type'),
            $entityId === 'all' ? null : (int) $entityId,
            $request->validated('zone_code'),
            $request->validated('language_code')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'links_grouped_by_account_id' => $groupedUrlsDTO->linksGroupedByEntityId,
                'master_links_grouped_by_type' => $groupedUrlsDTO->masterLinksGroupedByType,
                'links_groups' => [UrlTypeEnum::TRADING_PLATFORM->value, UrlTypeEnum::MOBILE->value, UrlTypeEnum::SPREAD_TYPE->value, UrlTypeEnum::SWAP->value, UrlTypeEnum::COMMISSION->value],
            ],

        ]);
    }

    /**
     * Create a broker affiliate link
     */
    public function createBrokerAffiliateLink(StoreAffiliateLinkRequest $request, int $broker_id): JsonResponse
    {

        $data = $request->validated();
        $storeAffiliateLinkDto = StoreAffiliateLinkDTO::fromValidated($data);
        //$isAdmin = app('isAdmin');

        $isAdmin = true;
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
        //affiliate links are stored in affiliate_links table
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
     * Create a account type url
     */
    public function createAccountTypeUrl(StoreAccountTypeUrlRequest $request, int $broker_id): JsonResponse
    {
        //$isAdmin = app('isAdmin');
        $isAdmin = true;
        $data = $request->validated();
        $createAccountTypeUrlsDto = AccountTypeUrlDTO::fromValidated($data);
        $urls = $this->urlService->createAccountTypeUrl($createAccountTypeUrlsDto, $broker_id, $isAdmin);

        return response()->json([
            'success' => true,
            'data' => $urls,
        ]);
    }

    public function updateAccountTypeUrls(StoreAccountTypeUrlRequest $request, int $broker_id, int $url_id)
    {
        //$isAdmin = app('isAdmin');
        $isAdmin = true;
        $data = $request->validated();
        $updateAccountTypeUrlsDto = AccountTypeUrlDTO::fromValidated($data);
        $url = $this->urlService->updateAccountTypeUrl($updateAccountTypeUrlsDto, $url_id, $isAdmin);
        return response()->json([
            'success' => true,
            'data' => $url,
        ]);
    }
}
