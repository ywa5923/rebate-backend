<?php

namespace Modules\Brokers\Services;

use App\Exceptions\ApiException;
use App\Utilities\ModelHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Brokers\Enums\UrlTypeEnum;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Repositories\AccountTypeRepository;
use Modules\Brokers\Repositories\UrlRepository;
use Modules\Brokers\Transformers\AccountTypeUrlsResource;
use Modules\Brokers\Transformers\URLResource;

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

        return $urls->groupBy('url_type')->map(fn ($items) => $items->values());
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

    public function createBrokerAffiliateLink($broker_id, $data, $isAdmin)
    {
        $isMasterLink = $data['is_master_link'] ?? false;
        $urldata = [
            'broker_id' => $broker_id,
            'urlable_type' => AccountType::class,
            'urlable_id' => $isMasterLink ? null : $data['account_type_id'],
            'url_type' => $data['url_type'],
            'slug' => Str::slug($data['name']),

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

        $url = $this->repository->create($urldata);

        return $url;
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

        $url = $this->repository->update($url, $newUrlData);

        return $url;
    }

    public function deleteBrokerAffiliateLink($broker_id, $url_id)
    {
        $url = $this->repository->find($url_id);
        if (! $url || $url->broker_id != $broker_id) {
            throw new ApiException('URL not found', 404);
        }
        $url->delete();

        return true;
    }

    public function getBrokerAffiliateLinks($broker_id, string $lang, ?string $zone = null): array
    {

        $accountTypes = $this->accountTypeRepository->getAccountTypesWithIBLinks($broker_id, $lang, $zone);

        $ibAffiliateUrls = $this->extractUrlsFromAccountTypes($accountTypes, UrlTypeEnum::IB_AFFILIATE_LINK);
        $subIbAffiliateUrls = $this->extractUrlsFromAccountTypes($accountTypes, UrlTypeEnum::SUB_IB_AFFILIATE_LINK);

        return [
            'account_types' => AccountTypeUrlsResource::collection($accountTypes),
            'ib_affiliate_urls' => URLResource::collection($ibAffiliateUrls),
            'sub_ib_affiliate_urls' => URLResource::collection($subIbAffiliateUrls),
        ];
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
