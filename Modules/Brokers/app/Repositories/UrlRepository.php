<?php

namespace Modules\Brokers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Enums\UrlTypeEnum;
use Modules\Brokers\Models\AccountType;
use Modules\Brokers\Models\Challenge;
use Modules\Brokers\Models\Url;

class UrlRepository
{
    protected Url $model;

    public function __construct(Url $model)
    {
        $this->model = $model;
    }

    /**
     * Create url
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data): Url
    {
        return $this->model->create($data);
    }

    /**s
     * Update url
     * @param Url $url
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(Url $url, array $data): Url
    {
        $url->update($data);

        return $url;
    }

    /**
     * Find url by id
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id): ?Url
    {
        return $this->model->find($id);
    }

    /**
     * Find urls by account type
     *
     * @param  int  $accountTypeId
     */
    public function findByAccountType($accountTypeId): Collection
    {
        return $this->model->where('urlable_type', 'Modules\\Brokers\\Models\\AccountType')
            ->where('urlable_id', $accountTypeId)
            ->with('translations')
            ->get();
    }

    /**
     * Bulk create urls
     */
    public function bulkCreate(array $data): bool
    {
        return $this->model->insert($data);
    }

    /**
     * Get urls by entity
     */
    public function getUrlsByEntity(
        int $broker_id,
        string $entity_type,
        ?int $entity_id,
        ?string $zone_code = null,
        ?string $language_code = null): Collection
    {

        $builder = $this->model->newQuery()
            ->where('broker_id', $broker_id)
            ->where('urlable_type', $entity_type);

        if (is_numeric($entity_id)) {
            $builder->where('urlable_id', $entity_id);
        }

        if ($zone_code) {
            $builder->where(function ($query) use ($zone_code) {
                $query->whereHas('zone', function ($q) use ($zone_code) {
                    $q->where('zone_code', $zone_code);
                })->orWhere('is_invariant', '1');
            });
        } else {
            $builder->where('zone_id', null)->orWhere('zone_id', 0);
        }

        if ($language_code && $language_code != 'en') {
            $builder = $builder->with(['translations' => function ($query) use ($language_code) {
                $query->where('language_code', $language_code);
            }]);
        }

        return $builder->orderBy('id', 'desc')->get();
    }

    /**
     * Delete urls by url type
     *
     * @param  string  $urlType
     * @param  int  $brokerId
     */
    public function deleteByUrlType($urlType, $brokerId): bool
    {
        return $this->model->newQuery()->where('url_type', $urlType)
            ->where('broker_id', $brokerId)
            ->delete();
    }

    public function deleteByUrlableType($urlableType, $brokerId): bool
    {
        return $this->model->newQuery()->where('urlable_type', $urlableType)
            ->where('broker_id', $brokerId)
            ->delete();
    }

    /**
     * Find url by urlable type and id
     *
     * @param  string  $urlableType
     */
    public function findByUrlableTypeAndId($urlableType, ?int $urlableId, ?int $brokerId, bool $isPlaceholder = false, ?int $zoneId = null): ?Url
    {
        $qb = $this->model->newQuery()->where('urlable_type', $urlableType);

        if (isset($urlableId) && $urlableId != null) {
            $qb->where('urlable_id', $urlableId);
        } else {
            $qb->whereNull('urlable_id');
        }
        if (isset($brokerId)) {
            $qb->where('broker_id', $brokerId);
        } else {
            $qb->whereNull('broker_id');
        }
        if (isset($zoneId)) {
            $qb->where('zone_id', $zoneId);
        } else {
            $qb->whereNull('zone_id');
        }
        if (isset($isPlaceholder)) {
            $qb->where('is_placeholder', $isPlaceholder);
        }

        //dd($qb->getBindings(),$qb->toSql());
        return $qb->orderBy('id', 'desc')->first();

    }

    /**
     * Save affiliate link
     */
    public function saveAffiliateLink(
        ?int $challengeId,
        string $affiliateLink,
        string $affiliateLinkName,
        int $brokerId,
        ?bool $isAdmin = null,
        ?bool $isPlaceholder = false,
        ?int $zoneId = null,
    ): void {
        $field = ($isAdmin && ! $isPlaceholder) ? 'public_url' : 'url';

        //dd($isAdmin, $isPlaceholder);

        $this->create([
            'urlable_type' => Challenge::class,
            'urlable_id' => $challengeId ?? null,
            'url_type' => 'challenge-matrix',
            $field => $affiliateLink,
            'name' => $affiliateLinkName,
            'slug' => strtolower(str_replace(' ', '-', $affiliateLinkName)),
            'broker_id' => $brokerId,
            'is_placeholder' => $isPlaceholder,
            'zone_id' => $zoneId,
        ]);
    }

    /**
     * Upsert affiliate link (update or insert)
     * used in ChallengeService
     */
    public function upsertAffiliateLink(
        ?int $challengeId,
        ?string $affiliateLink,
        string $affiliateLinkName,
        ?int $brokerId,
        ?bool $isAdmin = null,
        ?bool $isPlaceholder = false,
        ?int $zoneId = null,
    ): void {
        // Check if record already exists
        $existingUrl = $this->findByUrlableTypeAndId(
            Challenge::class,
            $challengeId,
            $brokerId,
            $isPlaceholder,
            $zoneId
        );

        if ($existingUrl) {
            // Get the current value based on admin/placeholder status
            $oldAffiliateLinkValue = $isAdmin ? $existingUrl->public_url : $existingUrl->url;

            // Compare values and update if different
            if ($oldAffiliateLinkValue != $affiliateLink && ! is_null($affiliateLink)) {
                $updateData = [
                    ($isAdmin || $isPlaceholder) ? null : 'previous_url' => $existingUrl->url,
                    $isAdmin && ! $isPlaceholder ? 'public_url' : 'url' => $affiliateLink,
                    'is_updated_entry' => ($isAdmin || $isPlaceholder) ? false : true,
                ];

                // Remove null keys
                $updateData = array_filter($updateData, function ($key) {
                    return $key !== null;
                }, ARRAY_FILTER_USE_KEY);

                $existingUrl->update($updateData);
            } elseif (is_null($affiliateLink)) {
                $existingUrl->delete();
            }
        } else {
            // Create new record if affiliate link is not empty
            //and if old value is not found in database
            if (! empty($affiliateLink)) {
                $field = ($isAdmin && ! $isPlaceholder) ? 'public_url' : 'url';

                $this->create([
                    'urlable_type' => Challenge::class,
                    'urlable_id' => $challengeId ?? null,
                    'url_type' => 'challenge-matrix',
                    $field => $affiliateLink,
                    'name' => $affiliateLinkName,
                    'slug' => strtolower(str_replace(' ', '-', $affiliateLinkName)),
                    'broker_id' => $brokerId,
                    'is_placeholder' => $isPlaceholder,
                    'zone_id' => $zoneId,
                    'is_updated_entry' => $isAdmin ? 0 : 1,
                ]);
            }
        }
    }

    // public function getMasterAccountTypeLinks(int $broker_id, string $lang, ?string $zone = null): Collection
    // {
    //     return Url::query()
    //         ->where('broker_id', $broker_id)
    //         ->where('urlable_type', AccountType::class)
    //         ->whereNull('urlable_id')
    //         ->whereIn('url_type', [
    //             UrlTypeEnum::IB_AFFILIATE_LINK->value,
    //             UrlTypeEnum::SUB_IB_AFFILIATE_LINK->value,
    //             UrlTypeEnum::WEBPLATFORM->value,
    //         ])
    //         ->where(function ($q) use ($zone) {
    //             if ($zone === null) {
    //                 $q->whereNull('zone_id');
    //             } else {
    //                 $q->where('zone_id', $zone);
    //             }
    //         })
    //         ->with([
    //             'translations' => fn ($t) => $t->where('language_code', $lang),
    //             'associatedUrls' => fn ($q) => $zone === null
    //                 ? $q->wherePivotNull('zone_id')
    //                 : $q->wherePivot('zone_id', $zone),
    //         ])
    //         ->orderBy('url_type', 'asc')
    //         ->get();
    // }

    public function getMasterLinks(int $broker_id, string $lang, array $linkTypes, ?string $zone = null): Collection
    {

        return Url::query()
            ->where('urlable_type', AccountType::class)
            ->whereNull('urlable_id')
            ->where('broker_id', $broker_id)
            ->when($zone === null, fn ($q) => $q->whereNull('zone_id'), fn ($q) => $q->where('zone_id', $zone))
            ->whereIn('url_type', $linkTypes)
            ->with(['translations' => fn ($t) => $t->where('language_code', $lang)])
            ->get();

    }
}
