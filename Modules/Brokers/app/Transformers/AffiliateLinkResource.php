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
        //'id' => $this->id,
        'url' => $this->url,
        'public_url' => $this->public_url,
        'previous_url' => $this->previous_url,
        'is_updated_entry' => $this->is_updated_entry,
        'name' => $this->name,
        'slug' => $this->slug,
        'zone_id' => $this->zone_id,
       ];
       
    }
}
