<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Brokers\Transformers\DropdownOptionResource;
class DropdownListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            //'options' => DropdownOptionResource::collection($this->whenLoaded('dropdownOptions')),
            "options"=>$this->whenLoaded('dropdownOptions', function () {
                return $this->dropdownOptions->pluck('label')->implode(', ');
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
