<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utilities\TranslateTrait;
class FormBrokerOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    use TranslateTrait;
    public function toArray(Request $request): array
    {
       
        
        
       
        $meta_data = match(true) {
            !empty($this->meta_data) 
            && $this->meta_data!="[]"
            && $this->meta_data!="null"
             => json_decode($this->meta_data),
            !empty($this->dropdown_category_id) => $this->dropdownCategory->dropdownOptions->map(fn($option) => [
                'value' => $option->value,
                'label' => $option->label
            ]),
            default => null
        };
        //[{"label": "English", "value": "en"}, {"label": "Romania", "value": "ro"}]
       
        return [
            "id"=>$this->id,
            "slug"=>$this->slug,
            "name"=>$this->translateBrokerOption($this->slug),
            "data_type"=>$this->data_type,
            "form_type"=>$this->form_type,
            "required"=>$this->required,
            "placeholder"=>$this->placeholder,
            "tooltip"=>$this->tooltip,
            "min_constraint"=>$this->min_constraint,
            "max_constraint"=>$this->max_constraint,
            "meta_data"=>$meta_data,
            // "values"=>$this->values->map(function($value){
            //     return [
            //         "id"=>$value->id,
            //         "value"=>$value->value
            //     ];
            // })
          ];
    }
}
