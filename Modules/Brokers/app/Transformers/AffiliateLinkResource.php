<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AffiliateLinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'type' => $this->affiliate_type,
            'url' => $this->url,
            'public_url' => $this->public_url,
            'previous_url' => $this->previous_url,
            'name' => $this->name,
            'public_name' => $this->public_name,
            'previous_name' => $this->previous_name,
            'currency' => $this->currency,
            'public_currency' => $this->public_currency,
            'previous_currency' => $this->previous_currency,
            'is_updated_entry' => $this->is_updated_entry,
            'is_master_link' => $this->is_master_link,
            'account_type_id' => $this->account_type_id,
            'account_type_name' => $this->accountType?->optionValues?->first()?->translations?->first()?->value ?? $this->accountType?->optionValues?->first()?->value ?? 'unknown',
            'zone_id' => $this->zone_id,
            'metadata' => $this->metadata,
            'platform_urls' => AssociatedAffiliateLinkResource::collection($this->whenLoaded('platformUrls')),
        ];

    }
}
