<?php


namespace Modules\Brokers\Forms;

use App\Forms\Form;
use App\Forms\Field;

use Modules\Brokers\Models\Zone;

class DropdownListForm extends Form
{
    public function getFormData(): array
    {
        return [
            'name' => 'Dropdown List',
            'description' => 'Dropdown list form configuration',
            'sections' => [
                'definitions' => [
                    'label' => 'Dropdown List Definitions',
                    'fields' => [
                        'name' => Field::text('Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'description' => Field::text('Description', ['required'=>false, 'min'=>0, 'max'=>255]),
                        'options' => Field::array_fields('Options', [
                            'label' => Field::text('Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                            'value' => Field::text('Slug', ['required'=>true, 'min'=>3, 'max'=>100]),
                        ],['required'=>true,'min'=>1]),
                    ]
                    
                    ]
                
            ]
        ];
    }

   

}
