<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;

class SettingResource extends JsonResource
{
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            "key" => $this->key,
            "value" => $this->translate($this->key,true),
           "translations"=>TranslationResource::collection($this->whenLoaded('translations'))
          ];
    }
}
