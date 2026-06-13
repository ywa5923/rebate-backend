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
        ?int $brokerId,
        ?bool $isAdmin = null,
        ?bool $isPlaceholder = false,
        ?int $zoneId = null,
    ): void {
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
            $oldPreviousUrlValue = $existingUrl->previous_url??'';
            // Compare values and update if different
            if ($oldAffiliateLinkValue != $affiliateLink ) {
                $updateData = [
                    ($isAdmin || $isPlaceholder) ? null : 'previous_url' => ($existingUrl->url??'empty')."->".$oldPreviousUrlValue,
                    $isAdmin && ! $isPlaceholder ? 'public_url' : 'url' => $affiliateLink,
                    'is_updated_entry' => ($isAdmin || $isPlaceholder) ? false : true,
                ];

                // Remove null keys
                $updateData = array_filter($updateData, function ($key) {
                    return $key !== null;
                }, ARRAY_FILTER_USE_KEY);

                $existingUrl->update($updateData);
            } else if($isAdmin){
                //for admin we need to update is_updated_entry to 0 even if the public value is not changed
                //this will clear the red flag in the frontend
                $existingUrl->update([
                    'is_updated_entry' => 0,
                ]);
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
                   // 'is_updated_entry' => $isAdmin ? 0 : 1,
                ]);
            }
        }
    }

    

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

    public function delete(Url $url): bool
    {
        return $url->delete();
    }

    /**
     * Clone challenge affiliate links
     */
    public function cloneChallengeAffiliateLinks(int $challenge_id, array $new_challenge_ids, int $broker_id, bool $isAdmin, ?int $zone_id = null): bool
    {
        $affiliateLinkToClone = $this->model->newQuery()
            ->where('urlable_id', $challenge_id)
            ->where('urlable_type', Challenge::class)
            ->where('broker_id', $broker_id)
            ->when(
                $zone_id === null,
                fn ($query) => $query->whereNull('zone_id'),
                fn ($query) => $query->where('zone_id', $zone_id),
            )
            ->first();

        if (! $affiliateLinkToClone) {
            return false;
        }

        $insertData = [];
        $now = now();

        foreach ($new_challenge_ids as $newChallengeId) {
            $challengeAffiliateLink = $this->model->newQuery()
                ->where('urlable_id', $newChallengeId)
                ->where('urlable_type', Challenge::class)
                ->where('broker_id', $broker_id)
                ->when(
                    $zone_id === null,
                    fn ($query) => $query->whereNull('zone_id'),
                    fn ($query) => $query->where('zone_id', $zone_id),
                )->first();
            if ($challengeAffiliateLink) {
                $isUpdatedEntry = $challengeAffiliateLink->url == $affiliateLinkToClone->url ? 0 : 1;
                if ($isAdmin) {
                    $challengeAffiliateLink->update([
                        'public_url' => $affiliateLinkToClone->public_url,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    $challengeAffiliateLink->update([
                        'url' => $affiliateLinkToClone->url,
                        'is_updated_entry' => $isUpdatedEntry,
                        'previous_url' => $challengeAffiliateLink->url,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            } else {
                $attributes = $affiliateLinkToClone->toArray();
                unset($attributes['id']);

                if ($isAdmin) {
                    //clone only public_url which exist in $attributes, other keys are overwritten
                    $insertData[] = array_merge($attributes, [
                        'urlable_id' => $newChallengeId,
                        'urlable_type' => Challenge::class,
                        'is_updated_entry' => 0,
                        'previous_url' => null,
                        'url' => null,
                        'metadata' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    $insertData[] = array_merge($attributes, [
                        'urlable_id' => $newChallengeId,
                        'urlable_type' => Challenge::class,
                        'is_updated_entry' => 0,
                        'previous_url' => null,
                        'public_url' => null,
                        'metadata' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        return $this->model->newQuery()->insert($insertData);
    }
}
