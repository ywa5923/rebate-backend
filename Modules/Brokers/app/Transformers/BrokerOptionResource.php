<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\TranslateTrait;

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
            "dropdown_position"=>$this->dropdown_position,
            "load_in_dropdown"=>$this->load_in_dropdown,
            "allow_sorting"=>$this->allow_sorting,
            "data_type"=>$this->data_type,
            "form_type"=>$this->form_type
          ];
    }

    
}
