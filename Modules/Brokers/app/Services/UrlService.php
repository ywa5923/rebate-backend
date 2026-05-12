<?php

namespace Modules\Brokers\Services;

use App\Exceptions\ApiException;
use App\Utilities\ModelHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Brokers\DTOs\IbAffiliateLinksDTO;
use Modules\Brokers\DTOs\StoreAffiliateLinkDTO;
use Modules\Brokers\Enums\UrlTypeEnum;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Models\AffliliateLink;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Repositories\AccountTypeRepository;
use Modules\Brokers\Repositories\AffiliateLinkRepository;
use Modules\Brokers\Repositories\UrlRepository;
use Modules\Brokers\Transformers\AccountTypeUrlsCollection;
use Modules\Brokers\Transformers\AffiliateLinkCollection;

class UrlService
{
    protected UrlRepository $repository;

    protected AccountTypeRepository $accountTypeRepository;

    protected AffiliateLinkRepository $affiliateLinkRepository;

    public function __construct(
        UrlRepository $repository,
        AccountTypeRepository $accountTypeRepository,
        AffiliateLinkRepository $affiliateLinkRepository
    ) {
        $this->repository = $repository;
        $this->accountTypeRepository = $accountTypeRepository;
        $this->affiliateLinkRepository = $affiliateLinkRepository;
    }

    public function createMany($urlableTypeObject, string $entityType, array $urls, $isAdmin = false)
    {

        foreach ($urls as &$urlData) {

            if ($isAdmin) {
                $urlData['public_url'] = $urlData['url'];
                $urlData['public_name'] = $urlData['name'];
            } else {
                //TO BE DONE
                $urlData['is_updated_entry'] = 1;
            }
            $modelClass = ModelHelper::getModelClassFromSlug($entityType);
            $urlData['urlable_type'] = $modelClass;
            $urlData['urlable_id'] = $urlableTypeObject ? $urlableTypeObject->id : null;
            $urlData['slug'] = ! empty($urlData['slug']) ? $urlData['slug'] : Str::slug($urlData['name']);
            $urlData['created_at'] = now();
            $urlData['updated_at'] = now();
        }
        unset($urlData);

        $this->repository->bulkCreate($urls);

        return true;
    }

    public function updateMany(string $entityType, array $urls, int $broker_id, bool $isAdmin = false)
    {
        $updated = [];
        $modelClass = ModelHelper::getModelClassFromSlug($entityType);

        foreach ($urls as $urlData) {

            if (isset($urlData['id']) && is_numeric($urlData['id'])) {
                $existingUrl = $this->repository->find($urlData['id']);
                // dd($existingUrl);
                $urlData['urlable_type'] = $modelClass;
                if ($isAdmin) {
                    $urlData['public_url'] = $urlData['url'];
                    $urlData['public_name'] = $urlData['name'];
                    $urlData['is_updated_entry'] = 0;
                    unset($urlData['url']);
                    unset($urlData['name']);
                } else {

                    $urlData['is_updated_entry'] = 1;
                    trim($urlData['url']) !== trim($existingUrl->url) && $urlData['previous_url'] = $existingUrl->url;
                    trim($urlData['name']) !== trim($existingUrl->name) && $urlData['previous_name'] = $existingUrl->name;
                }

                //dd($urlData);
                if ($existingUrl && $existingUrl->urlable_type == $modelClass && $existingUrl->broker_id == $broker_id) {
                    $updated[] = $this->repository->update($existingUrl, $urlData);
                }
            }
        }

        return $updated;
    }

    public function getGroupedByType(AccountType $accountType)
    {
        $urls = $this->repository->findByAccountType($accountType->id);

        return $urls->groupBy('url_type')->map(fn ($items) => $items->values());
    }

    public function getUrlsByEntity(int $broker_id, string $entity_type, int $entity_id, ?string $zone_code, ?string $language_code)
    {

        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);

        $urls = $this->repository->getUrlsByEntity($broker_id, $modelClass, $entity_id, $zone_code, $language_code);

        return $urls;

    }

    public function validateData(int $broker_id, string $entity_type, int $entity_id, Request $request)
    {
        $validated = $request->validate([
            'zone_code' => 'sometimes|string',
            'language_code' => 'sometimes|string',
        ]);

        if (! is_numeric($entity_id) && $entity_id != 'all') {
          //  throw new ApiException('Entity ID must be a number or "all"', 422);
        }
        if (! is_numeric($broker_id)) {
            throw new ApiException('Broker ID must be a number', 422);
        }
        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);
        if (! class_exists($modelClass)) {
            throw new ApiException('Entity type must be a valid model class', 422);
        }
    }

    public function createAffiliateLink(StoreAffiliateLinkDTO $storeAffiliateLinkDto, int $broker_id, bool $isAdmin): AffliliateLink
    {
        $affiliateLinkRow = [
            'affiliate_type' => $storeAffiliateLinkDto->urlType,
            'broker_id' => $broker_id,
            'is_master_link' => $storeAffiliateLinkDto->isMasterLink,
            'account_type_id' => $storeAffiliateLinkDto->accountTypeId,
            'zone_id' => $storeAffiliateLinkDto->zoneId,
        ];

        if ($isAdmin) {
            //if the admin create the link set also the broker values i.e: name, url, currency
            $affiliateLinkRow['public_name'] = $storeAffiliateLinkDto->name;
            $affiliateLinkRow['public_url'] = $storeAffiliateLinkDto->url;
            $affiliateLinkRow['public_currency'] = $storeAffiliateLinkDto->currency;
            $affiliateLinkRow['name'] = $storeAffiliateLinkDto->name;
            $affiliateLinkRow['url'] = $storeAffiliateLinkDto->url;
            $affiliateLinkRow['currency'] = $storeAffiliateLinkDto->currency;
            $affiliateLinkRow['is_updated_entry'] = false;
        } else {
            $affiliateLinkRow['name'] = $storeAffiliateLinkDto->name;
            $affiliateLinkRow['url'] = $storeAffiliateLinkDto->url;
            $affiliateLinkRow['currency'] = $storeAffiliateLinkDto->currency;
            $affiliateLinkRow['is_updated_entry'] = true;
        }

        return DB::transaction(function () use ($affiliateLinkRow, $storeAffiliateLinkDto) {
            $affiliateLink = $this->affiliateLinkRepository->create($affiliateLinkRow);

            if (! $storeAffiliateLinkDto->isMasterLink) {
                // Non-master: attach platform URLs on the pivot table.
                $platformUrlIds = collect($storeAffiliateLinkDto->platformUrls->items)->pluck('id')->all();
                $affiliateLink->platformUrls()->sync($platformUrlIds);
            }

            return $affiliateLink;
        });
    }

    public function updateAffiliateLink(StoreAffiliateLinkDTO $updateAffiliateLinkDto, int $affiliateLinkId, int $broker_id, bool $isAdmin): AffliliateLink
    {
        $oldAffiliateLink = $this->affiliateLinkRepository->find($affiliateLinkId);
        if (! $oldAffiliateLink || $oldAffiliateLink->broker_id != $broker_id) {
            throw new ApiException('Affiliate link not found', 404);
        }
        $affiliateLinkRow = [
            'affiliate_type' => $updateAffiliateLinkDto->urlType,
            'broker_id' => $broker_id,
            'is_master_link' => $updateAffiliateLinkDto->isMasterLink,
            'account_type_id' => $updateAffiliateLinkDto->accountTypeId,
            'zone_id' => $updateAffiliateLinkDto->zoneId,
        ];

        if ($isAdmin) {
            $affiliateLinkRow['public_name'] = $updateAffiliateLinkDto->name;
            $affiliateLinkRow['public_url'] = $updateAffiliateLinkDto->url;
            $affiliateLinkRow['public_currency'] = $updateAffiliateLinkDto->currency;
            $affiliateLinkRow['is_updated_entry'] = false;
            //override metadata updated values
            $affiliateLinkRow['metadata'] = array_merge($oldAffiliateLink['metadata'] ?? [], [
                'updated_fields' => [],
            ]);

        } else {
            $updatedFields = [];

            //check if is master check was changed
            if ((bool)$updateAffiliateLinkDto->isMasterLink !== (bool)$oldAffiliateLink->is_master_link) {
                $affiliateLinkRow['is_updated_entry'] = true;
                $updatedFields[] = 'is_master_link';
            }

            $affiliateLinkRow['name'] = $updateAffiliateLinkDto->name;
            if (trim($oldAffiliateLink->name) !== trim($updateAffiliateLinkDto->name)) {
                $affiliateLinkRow['previous_name'] = $oldAffiliateLink->name;
                $affiliateLinkRow['is_updated_entry'] = true;
                $updatedFields[] = 'name';
            }
            $affiliateLinkRow['url'] = $updateAffiliateLinkDto->url;
            if (trim($oldAffiliateLink->url) !== trim($updateAffiliateLinkDto->url)) {
                $affiliateLinkRow['previous_url'] = $oldAffiliateLink->url;
                $affiliateLinkRow['is_updated_entry'] = true;
                $updatedFields[] = 'url';
            }
            $affiliateLinkRow['currency'] = $updateAffiliateLinkDto->currency;
            if (trim($oldAffiliateLink->currency) !== trim($updateAffiliateLinkDto->currency)) {
                $affiliateLinkRow['previous_currency'] = $oldAffiliateLink->currency;
                $affiliateLinkRow['is_updated_entry'] = true;
                $updatedFields[] = 'currency';
            }

            $oldPlatforms = $oldAffiliateLink->platformUrls()->pluck('id')->toArray();
            $newPlatforms = collect($updateAffiliateLinkDto->platformUrls->items)->pluck('id')->toArray();
            $platformUrlsDifference = $this->checkPlatformUrlsDifference($newPlatforms, $oldPlatforms);
            $previousValues = [];
            if ($platformUrlsDifference) {
                $previousValues['previous_platform_urls'] = $oldAffiliateLink->platformUrls()->pluck('name')->toArray();
                $updatedFields[] = 'platform_urls';
                $affiliateLinkRow['is_updated_entry'] = true;
            }
            if ($updateAffiliateLinkDto->accountTypeId !== $oldAffiliateLink->account_type_id) {
                $updatedFields[] = 'account_type_id';
                $previousValues['previous_account_type_name'] = $oldAffiliateLink->accountType?->optionValues?->first()?->value ?? 'unknown';
                $previousValues['previous_account_type_id'] = $oldAffiliateLink->account_type_id;
                $affiliateLinkRow['is_updated_entry'] = true;
            }
            if (! empty($updatedFields)) {
                $affiliateLinkRow['metadata'] = array_merge($affiliateLinkRow['metadata'] ?? [], [
                    'updated_fields' => $updatedFields,
                    'previous_relations_values' => $previousValues,
                ]);
            }
        }

        $affiliateLink = $this->affiliateLinkRepository->update($oldAffiliateLink, $affiliateLinkRow);

        $platformUrlIds = collect($updateAffiliateLinkDto->platformUrls->items)->pluck('id')->all();
        $affiliateLink->platformUrls()->sync($platformUrlIds);

        return $affiliateLink;
    }

    public function checkPlatformUrlsDifference(array $newPlatforms, array $oldPlatforms): ?array
    {

        $added = array_diff($newPlatforms, $oldPlatforms);
        $removed = array_diff($oldPlatforms, $newPlatforms);

        if (empty($added) && empty($removed)) {
            return null; // No changes
        }
        if (! empty($added) && empty($removed)) {
            return ['added' => $added];
        }
        if (empty($added) && ! empty($removed)) {
            return ['removed' => $removed];
        }

        return null;
    }

   

    public function deleteAffiliateLink(int $brokerId, int $affiliateLinkId): bool
    {
        $affiliateLink = $this->affiliateLinkRepository->find($affiliateLinkId);

        if (! $affiliateLink || $affiliateLink->broker_id !== $brokerId) {
            throw new ApiException('Affiliate link not found', 404);
        }

        return DB::transaction(function () use ($affiliateLink) {
            return $this->affiliateLinkRepository->delete($affiliateLink);
        });
    }

    public function getAccountTypesWithPlatformLinks(int $broker_id, string $lang, ?string $zone = null): AccountTypeUrlsCollection
    {
        $accountTypes = $this->accountTypeRepository->getAccountTypesWithPlatformLinks($broker_id, $lang, $zone);
        $masterLinks = $this->repository->getMasterLinks($broker_id, $lang, [UrlTypeEnum::WEBPLATFORM->value], $zone);

        //attach master links to each account type platform urls
        $accountTypes->each(function ($accountType) use ($masterLinks) {
            $mergedUrls = $accountType->urls
                ->concat($masterLinks)
                ->unique('id')
                ->values();
            $accountType->setRelation('urls', $mergedUrls);
        });

        return new AccountTypeUrlsCollection($accountTypes);
    }

    public function getAffiliateLinks(int $broker_id, string $lang, ?string $zone = null): IbAffiliateLinksDTO
    {
        $affiliateLinks = $this->affiliateLinkRepository->getBrokerAffiliateLinks($broker_id, $lang, $zone);
        $ibAffiliateUrls = $affiliateLinks->where('affiliate_type', UrlTypeEnum::IB_AFFILIATE_LINK->value);
        $subIbAffiliateUrls = $affiliateLinks->where('affiliate_type', UrlTypeEnum::SUB_IB_AFFILIATE_LINK->value);

        return new IbAffiliateLinksDTO(
            ibAffiliateUrls: new AffiliateLinkCollection($ibAffiliateUrls->values()),
            subIbAffiliateUrls: new AffiliateLinkCollection($subIbAffiliateUrls->values()),
        );

    }
}
