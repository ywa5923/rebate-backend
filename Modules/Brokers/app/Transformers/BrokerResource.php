<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;

class BrokerResource extends JsonResource
{

    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
       // dd($this->dynamic_options_values);
        
        return [
            //"id"=>$this->id,
            "logo" =>$this->whenNotNull($this->logo),
            "favicon"=>$this->whenNotNull($this->favicon),
            "trading_name"=>$this->whenNotNull($this->translate("trading_name")),
            "home_url"=>$this->whenNotNull($this->home_url),
            "overall_rating"=>$this->whenNotNull($this->overall_rating),
            "user_rating"=>$this->whenNotNull($this->user_rating),
            "support_options"=>$this->whenNotNull($this->translate("support_options")),
            "account_type"=>$this->whenNotNull($this->translate("account_type")),
            "trading_instruments"=>$this->whenNotNull($this->translate("trading_instruments")),
            "account_currencies"=>$this->whenNotNull($this->account_currencies),
            "broker_type_id"=>$this->whenNotNull($this->broker_type_id),
            "default_language"=>$this->whenNotNull($this->default_language),
          // "translations"=>TranslationResource::collection($this->whenLoaded('translations')),
           "dynamic_options_values"=> $this->whenNotNull(DynamicOptionValueResource::collection ($this->whenLoaded('dynamicOptionsValues')))
           
          ];
    }

   
}
