<?php

namespace Modules\Brokers\Repositories;

use App\Exceptions\ApiException;
use Illuminate\Support\Collection;
use Modules\Brokers\Models\AffliliateLink;
use Modules\Translations\Models\Translation;

class AffiliateLinkRepository
{
    protected AffliliateLink $model;

    public function __construct(AffliliateLink $model)
    {
        $this->model = $model;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): AffliliateLink
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(AffliliateLink $affiliateLink, array $data): AffliliateLink
    {
        $affiliateLink->update($data);

        return $affiliateLink->refresh();
    }

    public function getBrokerAffiliateLinks(int $brokerId, string $lang, ?string $zone = null): Collection
    {
        return AffliliateLink::query()
            ->where('broker_id', $brokerId)
            ->when(
                $zone === null,
                fn ($query) => $query->whereNull('zone_id'),
                fn ($query) => $query->where('zone_id', $zone)
            )
            ->with([
                'platformUrls' => fn ($query) => $query->with([
                    'translations' => fn ($translationQuery) => $translationQuery->where('language_code', $lang),
                ]),
                'accountType' => fn ($query) => $query->with(['optionValues' => fn ($optionValueQuery) => $optionValueQuery->where('option_slug', 'account_type_name')]),
            ])
            //->orderBy('affiliate_type')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function find(int $id): ?AffliliateLink
    {
        return $this->model->newQuery()->with([
            'accountType' => fn ($query) => $query->with(['optionValues' => fn ($optionValueQuery) => $optionValueQuery->where('option_slug', 'account_type_name')]),
        ])->find($id);
    }

    public function delete(AffliliateLink $affiliateLink): void
    {
        Translation::query()
            ->where('translationable_type', AffliliateLink::class)
            ->where('translationable_id', $affiliateLink->id)
            ->delete();

        $affiliateLink->platformUrls()->detach();

        if (! $affiliateLink->delete()) {
            throw new ApiException('Failed to delete affiliate link.');
        }
    }
}
