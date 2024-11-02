<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;

class DynamicOptionValueResource extends JsonResource
{
   use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            "option_slug" => $this->option_slug,
            "value" => $this->translate($this->option_slug,true),
            "status" => $this->whenNotNull($this->status),
            "status_message" => $this->whenNotNull($this->translate("status_message")),
           // "default_loading" => $this->default_loading,
            "metadata" => $this->whenNotNull($this->translateOptionMeta($this->option_slug)),
            "translations"=>TranslationResource::collection($this->whenLoaded('translations'))
          ];

    }
}
