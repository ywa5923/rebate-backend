<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;

class BrokerOptionResource extends JsonResource
{
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
          
            $this->slug=>$this->translateBrokerOption($this->slug),
            "default_loading"=>$this->default_loading,
            "default_loading_position"=>$this->default_loading_position,
            "dropdown_position"=>$this->dropdown_position
          ];
    }

    
}
