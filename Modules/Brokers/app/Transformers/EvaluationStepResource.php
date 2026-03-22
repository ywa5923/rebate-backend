<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Brokers\Transformers\OptionValueResource;
class EvaluationStepResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'approved' => $this->approved,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'option_values' => OptionValueResource::collection($this->optionValues),
        ];
    }
}
