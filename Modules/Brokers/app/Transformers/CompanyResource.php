<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use Modules\Brokers\Transformers\RegulatorResource;
class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'option_values' => OptionValueResource::collection($this->whenLoaded('optionValues')),
            'regulators' => RegulatorResource::collection($this->whenLoaded('regulators')),
        ];
    }
}
