<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utilities\TranslateTrait;
use Modules\Brokers\Models\AccountType;

class URLResource extends JsonResource
{
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
       
        
       $result = [
            // Basic Information
            "id" => $this->id,
            "url_type" => $this->url_type,
            "url" => $this->url,
            "public_url" => $this->public_url,
            "previous_url" => $this->previous_url,

           // "url_p" => $this->translateProp("url_p"),
            "name" => $this->translateProp("name"),
            "public_name" => $this->translateProp("public_name"),
            "previous_name" => $this->translateProp("previous_name"),
            "is_updated_entry" => $this->is_updated_entry,

           // "name_p" => $this->translateProp("name_p"),
            "slug" => $this->slug,
            "description" => $this->translateProp("description")    ,
            
            // Configuration
            "is_invariant" => $this->is_invariant,
            "category_position" => $this->category_position,
            
            // Polymorphic Relationship
            //"urlable_type" => $this->urlable_type,
            //"urlable_id" => $this->urlable_id,
           
            "is_master_link" => $this->urlable_id===null,

            
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
        
        if ($this->urlable_type==AccountType::class) {
            $result["account_type_id"] = $this->urlable_id;
        }
        if($this->account_type_name){
            $result["account_type_name"] = $this->account_type_name;
        }
        return $result;
    }
}
