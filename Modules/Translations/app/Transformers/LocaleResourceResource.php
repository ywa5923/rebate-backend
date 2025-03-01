<?php

namespace Modules\Translations\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocaleResourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            "key"=>$this->key,
            "language" =>$this->language_code,
            "zone"=>$this->whenNotNull($this->zone_code),
            "value"=>json_decode($this->whenNotNull($this->value),true),
            "description"=>$this->whenNotNull($this->description),
          ];
    }
}
