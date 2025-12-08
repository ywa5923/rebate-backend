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
       $aditional = $this->additional['detail'] ?? false;
      
       $baseData = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
        ];
        if($aditional == 'form-edit'){
            $baseData['options'] = DropdownOptionResource::collection($this->whenLoaded('dropdownOptions'))->collect()->map(function ($option) {
                return [
                    'label' => $option->label,
                    'value' => $option->value,
                ];
            });
        }else{
            $baseData['options'] = $this->whenLoaded('dropdownOptions', function () {
                return $this->dropdownOptions->pluck('label')->implode(', ');
            });
        }
        return $baseData;
    }
}
