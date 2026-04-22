<?php

namespace Modules\Brokers\Services;

use App\Exceptions\ApiException;
use App\Utilities\ModelHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Brokers\Enums\UrlTypeEnum;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Models\UrlAssociations;
use Modules\Brokers\Repositories\AccountTypeRepository;
use Modules\Brokers\Repositories\UrlRepository;
use Modules\Brokers\Transformers\AccountTypeUrlsResource;
use Modules\Brokers\Transformers\URLResource;
use Modules\Brokers\Models\Url;

class UrlService
{
    protected $repository;

    protected $accountTypeRepository;

    public function __construct(UrlRepository $repository, AccountTypeRepository $accountTypeRepository)
    {
        $this->repository = $repository;
        $this->accountTypeRepository = $accountTypeRepository;
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

    public function updateMany(string $entityType, array $urls, $broker_id, $isAdmin = false)
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

        return $urls->groupBy('url_type')->map(fn($items) => $items->values());
    }

    public function getUrlsByEntity($broker_id, $entity_type, $entity_id, $zone_code, $language_code)
    {

        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);

        $urls = $this->repository->getUrlsByEntity($broker_id, $modelClass, $entity_id, $zone_code, $language_code);

        return $urls;

        // $builder = Url::with('translations')->where('broker_id', $broker_id)
        // ->where('urlable_type', $modelClass);
        // if(is_numeric($entity_id)){
        //     $builder->where('urlable_id', $entity_id);
        // }
        // $urls = $builder->get();
    }

    public function validateData($broker_id, $entity_type, $entity_id, $request)
    {
        $validated = $request->validate([
            'zone_code' => 'sometimes|string',
            'language_code' => 'sometimes|string',
        ]);

        if (! is_numeric($entity_id) && $entity_id != 'all') {
            throw new ApiException('Entity ID must be a number or "all"', 422);
        }
        if (! is_numeric($broker_id)) {
            throw new ApiException('Broker ID must be a number', 422);
        }
        $modelClass = ModelHelper::getModelClassFromSlug($entity_type);
        if (! class_exists($modelClass)) {
            throw new ApiException('Entity type must be a valid model class', 422);
        }
    }

    public function createBrokerAffiliateLink(int $broker_id, array $data, bool $isAdmin, ?int $zone_id = null): Url
    {

        $isMasterLink = $data['is_master_link'] ?? false;
        $urldata = [
            'broker_id' => $broker_id,
            'urlable_type' => AccountType::class,
            'urlable_id' => $isMasterLink ? null : $data['account_type_id'],
            'url_type' => $data['url_type'],
            'slug' => Str::slug($data['name']),
            'currency' => $data['currency'] ?? null,

        ];
        if ($isAdmin) {
            $urldata['public_url'] = trim($data['url']);
            $urldata['public_name'] = trim($data['name']);
            $urldata['url'] = trim($data['url']);
            $urldata['name'] = trim($data['name']);
        } else {
            $urldata['url'] = trim($data['url']);
            $urldata['name'] = trim($data['name']);
        }

        if (!$isMasterLink) {
            //the associatedUrls are inserted only for non master links
            return DB::transaction(function () use ($urldata, $data, $isAdmin, $zone_id) {
                $url = $this->repository->create($urldata);

                $platformUrlsData = $data['platform_urls'] ?? [];
                $syncAssociatedUrlsData = [];

                foreach ($platformUrlsData as $platformUrl) {
                    $pivotData = [
                        'is_public' => $isAdmin,
                        'is_updated_entry' => ! $isAdmin,
                        'association_type' => 'platform_url',
                    ];

                    if ($zone_id) {
                        $pivotData['zone_id'] = $zone_id;
                    }

                    $syncAssociatedUrlsData[$platformUrl['id']] = $pivotData;
                }

                $url->associatedUrls()->sync($syncAssociatedUrlsData);

                return $url;
            });
        } else {
            return $this->repository->create($urldata);
        }
    }

    public function updateBrokerAffiliateLink($broker_id, $url_id, $data, $isAdmin)
    {
        //first find the url by id and set previous url and name
        $newUrlData = [];
        $url = $this->repository->find($url_id);
        if (! $url || $url->broker_id != $broker_id) {
            throw new ApiException('URL not found', 404);
        }
        $isMasterLink = $data['is_master_link'] ?? false;
        $newUrlData['id'] = $url_id;
        $newUrlData['broker_id'] = $broker_id;
        $newUrlData['url_type'] = trim($data['url_type']);
        $newUrlData['urlable_id'] = $isMasterLink ? null : $data['account_type_id'];
        $newUrlData['urlable_type'] = AccountType::class;
        $newUrlData['slug'] = Str::slug($data['name']);
        //add previous values and is_updated_entry for brokers who are not admin
        if (! $isAdmin) {

            $newUrlData['url'] = trim($data['url']);
            $newUrlData['name'] = trim($data['name']);

            if ($url->url !== trim($data['url'])) {
                $newUrlData['previous_url'] = $url->url;
                $newUrlData['is_updated_entry'] = true;
            }
            if ($url->name !== trim($data['name'])) {
                $newUrlData['previous_name'] = $url->name;
                $newUrlData['is_updated_entry'] = true;
            }
            //detect if it is am updated entry

            if ($url->url_type !== trim($data['url_type'])) {

                $newUrlData['metadata'] = [
                    'previous_url_type' => $url->url_type,
                ];
                $newUrlData['is_updated_entry'] = true;
            }

            if ($url->urlable_id !== $data['account_type_id']) {

                $newUrlData['metadata'] = array_merge($newUrlData['metadata'] ?? [], [
                    'previous_account_type_id' => $url->urlable_id,

                ]);
                $newUrlData['is_updated_entry'] = true;
            }
            if ($data['is_master_link']) {
                $newUrlData['metadata'] = array_merge($newUrlData['metadata'] ?? [], [
                    'previous_account_type_id' => $url->urlable_id,
                ]);
            }
        } else {
            $newUrlData['is_updated_entry'] = false;
            $newUrlData['public_url'] = trim($data['url']);
            $newUrlData['public_name'] = trim($data['name']);
        }



        $newPlatforms = collect($data['platform_urls'] ?? [])->pluck('id')->toArray();
        $oldPlatforms = $url->associatedUrls()->pluck('urls.id')->toArray();

        $platformUrlsDifference = $this->checkPlatformUrlsDifference($newPlatforms, $oldPlatforms);

        

            //if there is a difference in platform urls, we need to update the pivot table with the new platform urls and set the is_updated_entry to true for non admin users
            //first add previous platform urls to metadata if it is not already there
            if (! $isAdmin && $platformUrlsDifference) {

                $newUrlData['metadata'] = array_merge($newUrlData['metadata'] ?? [], [
                    'previous_platform_urls' => $url->associatedUrls()->pluck('urls.name')->toArray(),
                ]);
            }
            $syncAssociatedUrlsData = [];

            foreach ($newPlatforms as $platformUrl) {
                $pivotData = [
                    'is_public' => $isAdmin,
                    'is_updated_entry' => !$isAdmin && $platformUrlsDifference ? true : false,
                    'association_type' => 'platform_url',
                ];

                
                $syncAssociatedUrlsData[$platformUrl] = $pivotData;
                
            }

            $url->associatedUrls()->sync($syncAssociatedUrlsData);

            $this->repository->update($url, $newUrlData);

        

       
    }



    public function checkPlatformUrlsDifference($newPlatforms, $oldPlatforms)
    {


        $added = array_diff($newPlatforms, $oldPlatforms);
        $removed = array_diff($oldPlatforms, $newPlatforms);

        if (empty($added) && empty($removed)) {
            return null; // No changes
        }
        if (!empty($added) && empty($removed)) {
            return ['added' => $added];
        }
        if (empty($added) && !empty($removed)) {
            return ['removed' => $removed];
        }
    }

    public function deleteBrokerAffiliateLink($broker_id, $url_id)
    {
        $url = $this->repository->find($url_id);
        if (! $url || $url->broker_id != $broker_id) {
            throw new ApiException('URL not found', 404);
        }

        DB::transaction(function () use ($url) {
            $url->associatedUrls()->detach();

            UrlAssociations::query()
                ->where('associated_url_id', $url->id)
                ->delete();

            $url->delete();
        });

        return true;
    }

    public function getBrokerAffiliateLinks($broker_id, string $lang, ?string $zone = null): array
    {

        $accountTypes = $this->accountTypeRepository->getAccountTypesWithIBLinks($broker_id, $lang, $zone);

        $ibAffiliateUrls = $this->extractUrlsFromAccountTypes($accountTypes, UrlTypeEnum::IB_AFFILIATE_LINK);
        $subIbAffiliateUrls = $this->extractUrlsFromAccountTypes($accountTypes, UrlTypeEnum::SUB_IB_AFFILIATE_LINK);

        $masterLinks=$this->repository->getMasterAccountTypeLinks($broker_id, $lang, $zone);

        $ibAffiliateUrls = $ibAffiliateUrls->merge($masterLinks->where('url_type', UrlTypeEnum::IB_AFFILIATE_LINK->value));
       $subIbAffiliateUrls = $subIbAffiliateUrls->merge($masterLinks->where('url_type', UrlTypeEnum::SUB_IB_AFFILIATE_LINK->value));
        return [
            'account_types' => AccountTypeUrlsResource::collection($accountTypes),
            'ib_affiliate_urls' => URLResource::collection($ibAffiliateUrls),
            'sub_ib_affiliate_urls' => URLResource::collection($subIbAffiliateUrls),
        ];
    }

    public function getUrlsByType($urls, UrlTypeEnum $urlType): Collection
    {
        return $urls->where('url_type', $urlType->value)->values();
    }

    public function extractUrlsFromAccountTypes($accountTypes, UrlTypeEnum $urlType): Collection
    {
        return $accountTypes->flatMap(function ($accountType) use ($urlType) {

            //check for account type name in first translations then in option values
            //we know from the AccountTypeRepository::getAccountTypesWithIBLinks that
            // only the option value containing the account type is selected

            $accountTypeName = $accountType->optionValues->first()?->translations?->first()?->value
                ?? $accountType->optionValues->first()?->value
                ?? 'unknown';

            //first get the account type name translated first,if not found, use the default value
            return $accountType->urls->whereIn('url_type', [
                $urlType->value,
            ])->values()->map(function ($url) use ($accountTypeName) {
                $url->account_type_name = $accountTypeName;

                return $url;
            });
        });
    }
}
