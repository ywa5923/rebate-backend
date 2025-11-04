<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utilities\TranslateTrait;

class BrokerOptionResource extends JsonResource
{
    use TranslateTrait;
    
    /**
     * Get the table column mapping configuration.
     * Maps server response columns to table configuration with visibility settings.
     *
     * @return array
     */
    public static function getTableColumnsMapping(): array
    {
        // Column order matches exactly the order in toArray() method (lines 61-89)
        return [
            'id' => ['label' => 'ID', 'visible' => true, 'sortable' => true, 'filterable' => false],
            'name' => ['label' => 'Name', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'slug' => ['label' => 'Slug', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'applicable_for' => ['label' => 'Applicable For', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'data_type' => ['label' => 'Data Type', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'form_type' => ['label' => 'Form Type', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'meta_data' => ['label' => 'Meta Data', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'for_crypto' => ['label' => 'For Crypto', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'for_brokers' => ['label' => 'For Brokers', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'for_props' => ['label' => 'For Props', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'category_name' => ['label' => 'Category', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'position_in_category' => ['label' => 'Position in Category', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'dropdown_list_attached' => ['label' => 'Dropdown List Attached', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'required' => ['label' => 'Required', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'placeholder' => ['label' => 'Placeholder', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'tooltip' => ['label' => 'Tooltip', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'min_constraint' => ['label' => 'Min Constraint', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'max_constraint' => ['label' => 'Max Constraint', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'load_in_dropdown' => ['label' => 'Load in Dropdown', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'dropdown_position' => ['label' => 'Dropdown Position', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'default_loading' => ['label' => 'Default Loading', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'default_loading_position' => ['label' => 'Default Loading Position', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'is_active' => ['label' => 'Is Active', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'allow_sorting' => ['label' => 'Allow Sorting', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'created_at' => ['label' => 'Created At', 'visible' => false, 'sortable' => true, 'filterable' => false],
            'updated_at' => ['label' => 'Updated At', 'visible' => false, 'sortable' => true, 'filterable' => false],
        ];
    }
    
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Check if detail parameter is set in additional data
        $useDetail = $this->additional['detail'] ?? false;
       
        if ($useDetail) {
            // Return full detail array with all columns
            return [
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
                'category_name' => $this->relationLoaded('category') && $this->category ? $this->category->name : null,
                'position_in_category' => $this->category_position,
                'dropdown_list_attached' => $this->relationLoaded('dropdownCategory') && $this->dropdownCategory ? $this->dropdownCategory->name : null,
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
            ];
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
