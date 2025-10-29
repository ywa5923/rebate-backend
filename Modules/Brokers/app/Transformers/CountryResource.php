<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if minimal flag is set (only return id and name)
        $minimal = $this->additional['minimal'] ?? false;
        
        if ($minimal) {
            return [
                'id' => $this->id,
                'name' => $this->name,
            ];
        }
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country_code' => $this->country_code,
            //'flag' => $this->flag,
            'zone_name' => $this->when($this->relationLoaded('zone') && $this->zone, $this->zone->name),
            'zone_code' => $this->when($this->relationLoaded('zone') && $this->zone, $this->zone->zone_code),
            'zone' => $this->when($this->relationLoaded('zone') && $this->zone, function () {
                return [
                    'id' => $this->zone->id,
                    'name' => $this->zone->name,
                    'zone_code' => $this->zone->zone_code,
                ];
            }),
            //'zone_id' => $this->zone_id,
            'brokers_count' => $this->whenLoaded('brokers', function () {
                return $this->brokers->count();
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

