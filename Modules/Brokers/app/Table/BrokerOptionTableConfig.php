<?php
namespace Modules\Brokers\Table;

use Modules\Brokers\Models\OptionCategory;
use Modules\Brokers\Models\DropdownCategory;
use Modules\Brokers\Models\BrokerOption;
final class BrokerOptionTableConfig implements TableConfigInterface
{
    /**
     * Get the table column mapping configuration.
     * Maps server response columns to table configuration with visibility settings.
     *
     * @return array
     */
    public  function columns(): array
    {
        // Column order matches exactly the order in toArray() method (lines 61-89)
        return [
            'id' => ['label' => 'ID', 'type' => 'number', 'visible' => true, 'sortable' => true, 'filterable' => false],
            'name' => ['label' => 'Name', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'slug' => ['label' => 'Slug', 'type' => 'text', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'applicable_for' => ['label' => 'Applicable For', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'data_type' => ['label' => 'Data Type', 'type' => 'text', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'form_type' => ['label' => 'Form Type', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'meta_data' => ['label' => 'Meta Data', 'type' => 'json', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'for_crypto' => ['label' => 'For Crypto', 'type' => 'boolean', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'for_brokers' => ['label' => 'For Brokers', 'type' => 'boolean', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'for_props' => ['label' => 'For Props', 'type' => 'boolean', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'category_name' => ['label' => 'Category', 'type' => 'text', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'position_in_category' => ['label' => 'Position in Category', 'type' => 'number', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'dropdown_list_attached' => ['label' => 'Dropdown List Attached', 'type' => 'text', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'required' => ['label' => 'Required', 'type' => 'boolean', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'placeholder' => ['label' => 'Placeholder', 'type' => 'text', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'tooltip' => ['label' => 'Tooltip', 'type' => 'text', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'min_constraint' => ['label' => 'Min Constraint', 'type' => 'number', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'max_constraint' => ['label' => 'Max Constraint', 'type' => 'number', 'visible' => false, 'sortable' => false, 'filterable' => true],
            'load_in_dropdown' => ['label' => 'Load in Dropdown', 'type' => 'boolean', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'dropdown_position' => ['label' => 'Dropdown Position', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'default_loading' => ['label' => 'Default Loading', 'type' => 'boolean', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'default_loading_position' => ['label' => 'Default Loading Position', 'type' => 'number', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'is_active' => ['label' => 'Is Active', 'type' => 'boolean', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'allow_sorting' => ['label' => 'Allow Sorting', 'type' => 'boolean', 'visible' => false, 'sortable' => true, 'filterable' => true],
            'created_at' => ['label' => 'Created At', 'type' => 'text', 'visible' => false, 'sortable' => true, 'filterable' => false],
            'updated_at' => ['label' => 'Updated At', 'type' => 'text', 'visible' => false, 'sortable' => true, 'filterable' => false],
        ];
    }
    public function filters(): array
    {

        
        return [
            'name' => ['type' => 'text', 'placeholder' => 'Search by name'],
            'applicable_for' => [
                'type' => 'select', 
                'options' => $this->getDistinctOptions('applicable_for')
            ],
            'data_type' => [
                'type' => 'select', 
                'options' => $this->getDistinctOptions('data_type')
            ],
            'form_type' => [
                'type' => 'select',
                'tooltip' => 'Filter by form type',
                'options' => $this->getDistinctOptions('form_type')
            ],
            'for_brokers' => [
                'type' => 'select',
                'options' => [
                    '1' => 'Yes',
                    '0' => 'No',
                ]
            ],
            'for_crypto' => [
                'type' => 'select',
                'options' => [
                    '1' => 'Yes',
                    '0' => 'No',
                ]
            ],
            'for_props' => [
                'type' => 'select',
                'options' => [
                    '1' => 'Yes',
                    '0' => 'No',
                ]
            ],
            'required' => [
                'type' => 'select',
                'options' => [
                    '1' => 'Yes',
                    '0' => 'No',
                ]
            ],
            'load_in_dropdown' => [
                'type' => 'select',
                'tooltip' => 'This filter shows options that are loaded in dropdown list which is opened when the user click Select Columns button in a dynamic table',
                'options' => [
                    '1' => 'Yes',
                    '0' => 'No',
                ]
            ],
            'default_loading' => [
                'type' => 'select',
                'tooltip' => 'This filter shows options that are loaded by default in brokers dynamic table',
                'options' => [
                    '1' => 'Yes',
                    '0' => 'No',
                ]
            ],
            'category_name' => [
                'type' => 'select',
                'tooltip' => 'Filter by option category name',
                'options' => OptionCategory::all()->map(function($category) {
                    return [
                        'value' => $category->id,
                        'label' => $category->name,
                    ];
                })->toArray()
            ],
            'dropdown_list_attached' => [
                'type' => 'select',
                'tooltip' => 'Filter by dropdown list attached',
                'options' => DropdownCategory::all()->map(function($dropdownCategory) {
                    return [
                        'value' => $dropdownCategory->id,
                        'label' => $dropdownCategory->name,
                    ];
                })->toArray()
            ]
        ];
    }


    public function getDistinctOptions(string $column): array
    {
       return BrokerOption::select($column)
                    ->distinct()
                    ->orderBy($column)
                    ->get()
                    ->map(function($option) use ($column): array {
                        return [
                            'value' => $option->$column,
                            'label' => ucfirst(str_replace('_', ' ', $option->$column)),
                        ];
                    })
                    ->toArray();
    }
}
