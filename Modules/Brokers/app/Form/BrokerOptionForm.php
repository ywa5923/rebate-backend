<?php

namespace Modules\Brokers\Form;

use App\Form\Form;
use App\Form\Field;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\OptionCategory;
use Modules\Brokers\Models\DropdownCategory;
class BrokerOptionForm extends Form
{
    public function getFormData(): array
    {
        return [
            'name' => 'Broker Option',
            'description' => 'Broker option form configuration',
            'sections' => [
                'definitions' => [
                    'label' => 'Definitions',
                    'fields' => [
                        'name' => Field::text('Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'slug' => Field::text('Slug', ['required'=>true, 'min'=>3, 'max'=>100]),
                    ],
                ],
                'form_settings' => [
                    'label' => 'Form Settings',
                    'fields' => [
                        'form_type' => Field::select('Form Type', 'string', $this->getFormTypes(), ['required'=>true]),
                        'data_type' => Field::select('Data Type', 'string', $this->getDataTypes(), ['required'=>true]),
                        'dropdown_category_id' => Field::select('Dropdown List Attached', 'numeric',$this->getDropdownCategories(),['required'=>false]),
                        'placeholder' => Field::text('Placeholder', ['required'=>false,'nullable'=>true]),
                        'tooltip' => Field::text('Tooltip', ['required'=>false,'nullable'=>true]),
                    ],
                ],
                'applicability' => [
                    'label' => 'Applicability',
                    'fields' => [
                        'applicable_for' => Field::select(
                            'Applicable For',
                            'string',
                            //$this->getDistinctOptions(BrokerOption::class, 'applicable_for')
                            $this->getApplicableForOptions()
                          ,['required'=>false,'nullable'=>true]),
                        'for_brokers' => Field::select('For Brokers', 'numeric',$this->booleanOptions(),['required'=>true]),
                        'for_props'   => Field::select('For Props', 'numeric',$this->booleanOptions(),['required'=>true]),
                        'for_crypto'  => Field::select('For Crypto', 'numeric',$this->booleanOptions(),['required'=>true]),
                        // 'test_array' => Field::array_fields('Test Array', [
                        //     'field_name' => Field::text('Field Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                        //     'field_slug' => Field::text('Field Value', ['required'=>true, 'min'=>3, 'max'=>100]),
                        // ]),
                    ],
                ],
                'table_settings' => [
                    'label' => 'Table Settings',
                    'fields' => [
                        'load_in_dropdown' => Field::select('Load In Dropdown', 'numeric',$this->booleanOptions(),['required'=>true]),
                        'dropdown_position' => Field::number('Dropdown Position', ['required'=>true]),
                        'default_loading' => Field::select('Default Loading', 'numeric',$this->booleanOptions(),['required'=>true]),
                        'default_loading_position' => Field::number('Default Loading Position', ['required'=>true]),
                        'allow_sorting' => Field::select('Allow Sorting', 'numeric',$this->booleanOptions(),['required'=>true]),
                    ],
                ],
                'category_settings' => [
                    'label' => 'OptionCategory Settings',
                    'fields' => [
                        'option_category_id' => Field::select('Option Category', 'numeric',$this->getOptionCategories(),['required'=>true]),
                        'category_position' => Field::number('Category Position', ['required'=>true]),
                    ],
                ],
                'constraints' => [
                    'label' => 'Constraints',
                    'fields' => [
                        'min_constraint' => Field::number('Min Constraint', ['required'=>false,'min'=>100]),
                        'max_constraint' => Field::number('Max Constraint', ['required'=>false,'min'=>100]),
                        'required' => Field::select('Required', 'numeric',$this->booleanOptions(),['required'=>true]),
                    ],
                ],
            ],
        ];
    }

    private function booleanOptions(): array
    {
        return [
            
            ['value' => 0, 'label' => 'No'],
            ['value' => 1, 'label' => 'Yes'],
        ];
    }

    private function getFormTypes(): array
    {
        return [
            ['value' => 'textarea', 'label' => 'Textarea'],
            ['value' => 'number', 'label' => 'Number'],
            ['value' => 'url', 'label' => 'URL'],
            ['value' => 'image', 'label' => 'Image'],
            ['value' => 'checkbox', 'label' => 'Checkbox'],
            ['value' => 'single_select', 'label' => 'Single Select'],
            ['value' => 'multiple_select', 'label' => 'Multiple Select'],
            ['value' => 'numberWithUnit', 'label' => 'Number With Unit'],
            ['value' => 'string', 'label' => 'String'],
            ['value' => 'notes', 'label' => 'Notes'],
       
        ];
    }

    public function getDataTypes(): array
    {
        return [
            ['value' => 'text', 'label' => 'Text'],
            ['value' => 'number', 'label' => 'Number'],
            ['value' => 'decimal', 'label' => 'Decimal'],
            ['value' => 'integer', 'label' => 'Integer'],
            ['value' => 'string', 'label' => 'String'],
            ['value' => 'boolean', 'label' => 'Boolean'],
            ['value' => 'date', 'label' => 'Date'],
            ['value' => 'time', 'label' => 'Time'],
            ['value' => 'datetime', 'label' => 'DateTime'],
            ['value' => 'timestamp', 'label' => 'Timestamp'],
            ['value' => 'json', 'label' => 'JSON'],
           
        ];
    }

    private function getOptionCategories(): array
    {
        return OptionCategory::all()
            ->map(function ($category) {
                return ['value' => $category->id, 'label' => $category->name];
            })
            ->values()
            ->all();
    }

    private function getDropdownCategories(): array
    {
        return DropdownCategory::all()
            ->map(function ($category) {
                return ['value' => $category->id, 'label' => $category->name];
            })
            ->values()
            ->all();
    }

    private function getApplicableForOptions(): array
    {
        return [
            ['value' => 'broker', 'label' => 'Broker'],
            ['value' => 'company', 'label' => 'Company'],
            ['value' => 'account_type', 'label' => 'Account Type'],
            ['value' => 'promotion', 'label' => 'Promotion'],
            ['value' => 'contest', 'label' => 'Contest'],
            ['value' => 'other', 'label' => 'Other'],
        ];
    }
}
