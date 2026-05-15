<?php

namespace Modules\Brokers\Transformers;

use App\Utilities\TranslateTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegulatorResource extends JsonResource
{
    use TranslateTrait;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'acronym' => $this->acronym,
            'country' => $this->country,
            'country_code' => $this->country_code,
            'zone' => $this->zone,
            'tier_classification' => $this->tier_classification,
            'rating' => $this->rating,
            'investor_protection_scheme' => $this->investor_protection_scheme,
            'compensation_scheme' => $this->compensation_scheme,
            'retail_leverage_restrictions' => $this->retail_leverage_restrictions,
            'website' => $this->website,
            'year_established' => $this->year_established,
            'jurisdiction_type' => $this->jurisdiction_type,
            'notes' => $this->translateProp('notes'),
            'description' => $this->translateProp('description'),
            'status' => $this->status,
            'status_reason' => $this->status_reason,
            'is_invariant' => $this->is_invariant,
            'zone_id' => $this->zone_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
