<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utilities\TranslateTrait;

class BrokerOptionResource extends JsonResource
{
    use TranslateTrait;
    
   
    
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Check if detail parameter is set in additional data
        $useDetail = $this->additional['detail'] ?? false;
       
        if ($useDetail) {
            $tableListDetails=[];
            if($useDetail == "table-list"){
                $tableListDetails=[
                    'category_name' => $this->relationLoaded('category') && $this->category ? $this->category->name : null,
                    'position_in_category' => $this->category_position,
                    'dropdown_list_attached' => $this->relationLoaded('dropdownCategory') && $this->dropdownCategory ? $this->dropdownCategory->name : null,
                ];
            }else if($useDetail == "form-edit"){
                $tableListDetails=[
                    'option_category_id' => $this->option_category_id,
                    'category_position'=>$this->category_position,
                    'dropdown_category_id' => $this->dropdown_category_id,
                ];
            }
            
            // Return full detail array with all columns
            return array_merge($tableListDetails, [
                'id' => $this->id,
                'name' => $this->name,
                'slug' => $this->slug,
                'applicable_for' => $this->applicable_for,
                'data_type' => $this->data_type,
                'form_type' => $this->form_type,
                'meta_data' => $this->meta_data,
                'for_crypto' => $this->for_crypto,
                'for_brokers' => $this->for_brokers,
                'for_props' => $this->for_props,
                //'option_category_id' => $this->option_category_id,
                //'category_position'=>$this->category_position,
                //'dropdown_category_id' => $this->dropdown_category_id,
                'required' => $this->required,
                'placeholder' => $this->placeholder,
                'tooltip' => $this->tooltip,
                'min_constraint' => $this->min_constraint,
                'max_constraint' => $this->max_constraint,
                'load_in_dropdown' => $this->load_in_dropdown,
                'dropdown_position' => $this->dropdown_position,
                'default_loading' => $this->default_loading,
                'default_loading_position' => $this->default_loading_position,
                'is_active' => $this->publish,
                //'position' => $this->position,
                'allow_sorting' => $this->allow_sorting,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]);
        }
        
        // Return original translation-based array
        return [
            $this->slug => $this->translateBrokerOption($this->slug),
            "slug" => $this->slug,
            "name" => $this->translateBrokerOption($this->slug),
            "default_loading" => $this->default_loading,
            "default_loading_position" => $this->default_loading_position,
            "dropdown_position" => $this->dropdown_position,
            "load_in_dropdown" => $this->load_in_dropdown,
            "allow_sorting" => $this->allow_sorting,
            "data_type" => $this->data_type,
            "form_type" => $this->form_type,
            'tooltip' => $this->tooltip,
            'placeholder' => $this->placeholder,
            'required' => $this->required,
        ];
    }
}
