<?php

namespace Modules\Translations\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
          "id"=>$this->id,
          "language" =>$this->language_code,
          "translation_type"=>$this->whenNotNull($this->translation_type),
          "property"=>$this->whenNotNull($this->property),
          "value"=>$this->whenNotNull($this->value),
          "metadata"=>$this->whenNotNull($this->metadata),
         
        ];
    }
}
