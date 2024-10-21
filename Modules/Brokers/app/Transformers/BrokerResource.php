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
            "id"=>$this->id,
            //"logo" =>$this->whenNotNull($this->logo),
            // "favicon"=>$this->whenNotNull($this->favicon),
            // "trading_name"=>$this->translate("trading_name")."**##**".$this->home_url,
            // "home_url"=>$this->home_url,
            // "overall_rating"=>$this->overall_rating??0,
            // "user_rating"=>$this->user_rating,
            // "support_options"=>$this->translate("support_options"),
            // "account_type"=>$this->translate("account_type"),
            // "trading_instruments"=>$this->translate("trading_instruments"),
            // "account_currencies"=>$this->account_currencies,
            // "broker_type_id"=>$this->whenNotNull($this->broker_type_id),
            // "default_language"=>$this->whenNotNull($this->default_language),
          // "translations"=>TranslationResource::collection($this->whenLoaded('translations')),
          // "dynamic_options_values"=> $this->whenNotNull(DynamicOptionValueResource::collection ($this->whenLoaded('dynamicOptionsValues'))),
           "dynamic_options_values"=> DynamicOptionValueResource::collection ($this->whenLoaded('dynamicOptionsValues')),
           "companies"=>CompanyResource::collection($this->whenLoaded('companies')),
           "regulators"=>RegualtorResource::collection($this->whenLoaded('regulators'))
          ];
    }

   
}
