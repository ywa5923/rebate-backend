<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CostDiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            //'id' => $this->id,
            'public_value' => $this->public_value,
            'broker_value' => $this->broker_value,
            'old_value' => $this->old_value,
            'is_updated_entry' => $this->is_updated_entry,
            'zone_id' => $this->zone_id,
        ];
    }
}
;