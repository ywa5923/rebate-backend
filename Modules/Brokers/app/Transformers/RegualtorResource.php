<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utilities\TranslateTrait;

class RegualtorResource extends JsonResource
{
    use TranslateTrait;

   // public function toArray(Request $request): array
    // {
    //     return [
    //         //"id"=>$this->id,
    //         "abreviation" => $this->abreviation,
    //         "country" => $this->translateProp("country")
    //     ];
    // }
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'abreviation' => $this->abreviation,
            'country' => $this->translateProp("country"),
            'country_p' => $this->country_p,
            'description' => $this->description,
            'description_p' => $this->description_p,
            'rating' => $this->rating,
            'rating_p' => $this->rating_p,
            'capitalization' => $this->capitalization,
            'capitalization_p' => $this->capitalization_p,
            'segregated_clients_money' => $this->segregated_clients_money,
            'segregated_clients_money_p' => $this->segregated_clients_money_p,
            'deposit_compensation_scheme' => $this->deposit_compensation_scheme,
            'deposit_compensation_scheme_p' => $this->deposit_compensation_scheme_p,
            'negative_balance_protection' => $this->negative_balance_protection,
            'negative_balance_protection_p' => $this->negative_balance_protection_p,
            'rebates' => $this->rebates,
            'rebates_p' => $this->rebates_p,
            'enforced' => $this->enforced,
            'enforced_p' => $this->enforced_p,
            'max_leverage' => $this->max_leverage,
            'max_leverage_p' => $this->max_leverage_p,
            'website' => $this->website,
            'website_p' => $this->website_p,
            'status' => $this->status,
            'status_reason' => $this->status_reason,
            'brokers' => $this->whenLoaded('brokers', function () {
                return $this->brokers->map(function ($broker) {
                    return [
                        'id' => $broker->id,
                        'name' => $broker->name,
                        'trading_name' => $broker->trading_name,
                    ];
                });
            }),
            'translations' => $this->whenLoaded('translations', function () {
                return $this->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'language_code' => $translation->language_code,
                        'metadata' => $translation->metadata,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
}
