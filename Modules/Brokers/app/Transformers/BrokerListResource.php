<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrokerListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * used in BrokerController::getBrokerList method to return the broker list
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function toArray(Request $request): array
    {
        $returnArray=[
            'id' => $this->id,
            'broker_type' => $this->brokerType->name,
            'is_active' => $this->is_active,
            'country_id' => $this->country?->id,
            'zone_id' => $this->zone?->id,
            'country_code' => $this->country?->country_code ?? null,
            'zone_code' => $this->zone?->zone_code ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        foreach($this->dynamicOptionsValues as $dynamicOptionValue){
            $returnArray[$dynamicOptionValue->option_slug] = $dynamicOptionValue->value;
        }

        return $returnArray;
        
    }
}
