<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'zone_code' => $this->zone_code,
            'description' => $this->description,
            'countries_count' => $this->whenLoaded('countries', function () {
                return $this->countries->count();
            }),
            'brokers_count' => $this->whenLoaded('brokers', function () {
                return $this->brokers->count();
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

