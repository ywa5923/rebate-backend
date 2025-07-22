<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;
use App\Utilities\TranslateTrait;

class URLResource extends JsonResource
{
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        
        return [
            // Basic Information
            "id" => $this->id,
            "url_type" => $this->url_type,
            "url" => $this->translateProp("url"),
            "url_p" => $this->translateProp("url_p"),
            "name" => $this->translateProp("name"),
            "name_p" => $this->translateProp("name_p"),
            "slug" => $this->slug,
            "description" => $this->translateProp("description")    ,
            
            // Configuration
            "is_invariant" => $this->is_invariant,
            "category_position" => $this->category_position,
            
            // Polymorphic Relationship
            "urlable_type" => $this->urlable_type,
            "urlable_id" => $this->urlable_id,
            
            // Foreign Keys
            "option_category_id" => $this->option_category_id,
            "broker_id" => $this->broker_id,
            "zone_id" => $this->zone_id,
            
            // Translations (if loaded)
            //"translations" => TranslationResource::collection($this->whenLoaded('translations')),
            // Timestamps
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
