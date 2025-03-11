<?php

namespace Modules\Translations\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\TranslateTrait;
class LocaleResourceResource extends JsonResource
{
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
           // "id"=>$this->id,
            //"key"=>$this->key,
           // "translations"=>$this->translations()->get(),
            //"zone_code"=>$this->whenNotNull($this->zone_code),
            //"json_content"=>json_decode($this->whenNotNull($this->json_content),true),
            //"description"=>$this->whenNotNull($this->description),
            //"json_content"=>!empty($this->json_content)?json_decode($this->translateProp("json_content"),1):[],
            $this->section=>!empty($this->json_content)?json_decode($this->translateProp("json_content"),1):[],
            //"translations"=>TranslationResource::collection($this->whenLoaded('translations'))
          ];
    }
}
