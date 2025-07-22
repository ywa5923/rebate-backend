<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;
use App\Utilities\TranslateTrait;
use Modules\Brokers\Transformers\URLResource;
class AccountTypeResource extends JsonResource
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
            // "name" => $this->translateProp("name"),
            // "broker_type" => $this->broker_type,
            // "is_active" => $this->is_active,
            // "order" => $this->order,
            // "is_invariant" => $this->is_invariant,
            
            // // Commission Information
            // "commission_value" => $this->commission_value,
            // "commission_value_p" => $this->commission_value_p,
            // "commission_unit" => $this->translateProp("commission_unit"),
            // "commission_unit_p" => $this->translateProp("commission_unit_p"),
            
            // // Execution and Trading
            // "execution_model" => $this->execution_model,
            // "execution_model_p" => $this->execution_model_p,
            // "max_leverage" => $this->max_leverage,
            // "max_leverage_p" => $this->max_leverage_p,
            // "spread_type" => $this->translateProp("spread_type"),
            // "spread_type_p" => $this->translateProp("spread_type_p"),
            
            // // Deposit Requirements
            // "min_deposit_value" => $this->min_deposit_value,
            // "min_deposit_unit" => $this->translateProp("min_deposit_unit"),
            // "min_deposit_value_p" => $this->min_deposit_value_p,
            // "min_deposit_unit_p" => $this->translateProp("min_deposit_unit_p"),
            
            // // Trade Size Requirements
            // "min_trade_size_value" => $this->min_trade_size_value,
            // "min_trade_size_unit" => $this->translateProp("min_trade_size_unit"),
            // "min_trade_size_value_p" => $this->min_trade_size_value_p,
            // "min_trade_size_unit_p" => $this->translateProp("min_trade_size_unit_p"),
            
            // // Stopout Levels
            // "stopout_level_value" => $this->stopout_level_value,
            // "stopout_level_unit" => $this->translateProp("stopout_level_unit"),
            // "stopout_level_value_p" => $this->stopout_level_value_p,
            // "stopout_level_unit_p" => $this->translateProp("stopout_level_unit_p"),
            
            // // Trading Features
            // "trailing_stops" => $this->trailing_stops,
            // "trailing_stops_p" => $this->trailing_stops_p,
            // "allow_scalping" => $this->allow_scalping,
            // "allow_scalping_p" => $this->allow_scalping_p,
            // "allow_hedging" => $this->allow_hedging,
            // "allow_hedging_p" => $this->allow_hedging_p,
            // "allow_news_trading" => $this->allow_news_trading,
            // "allow_news_trading_p" => $this->allow_news_trading_p,
            // "allow_cent_accounts" => $this->allow_cent_accounts,
            // "allow_cent_accounts_p" => $this->allow_cent_accounts_p,
            // "allow_islamic_accounts" => $this->allow_islamic_accounts,
            // "allow_islamic_accounts_p" => $this->allow_islamic_accounts_p,
            
            // Relationships
            "broker_id" => $this->broker_id,
           /// "zone_id" => $this->zone_id,
           "option_values" => OptionValueResource::collection($this->whenLoaded('optionValues')),
            
          
           // "urls" => URLResource::collection($this->whenLoaded('urls')),
            
            // Translations (if loaded)
           // "translations" => TranslationResource::collection($this->whenLoaded('translations')),
            
            // Timestamps
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
