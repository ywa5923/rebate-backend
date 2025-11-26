<?php

namespace Modules\Brokers\Form;

use App\Form\Form;
use App\Form\Field;
use Modules\Brokers\Models\BrokerOption;

class BrokerOptionForm extends Form
{
    public function getFormData(): array
    {
        return [
            'name' => 'Broker Option',
            'description' => 'Broker option form configuration',
            'sections' => [
                'firstRow' => [
                    
                    'fields' => [
                        'name' => Field::text('Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'slug' => Field::text('Slug', ['required'=>true]),
                    ],
                ],
                'applicability' => [
                    'label' => 'Applicability',
                    'fields' => [
                        'applicable_for' => Field::select(
                            'Applicable For',
                            'string',
                            $this->getDistinctOptions(BrokerOption::class, 'applicable_for')
                        ),
                        'for_brokers' => Field::select('For Brokers', 'numeric',$this->booleanOptions()),
                        'for_props'   => Field::select('For Props', 'numeric',$this->booleanOptions()),
                        'for_crypto'  => Field::select('For Crypto', 'numeric',$this->booleanOptions()),
                        'test_array' => Field::array_fields('Test Array', [
                            'field_name' => Field::text('Field Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                            'field_slug' => Field::text('Field Value', ['required'=>true, 'min'=>3, 'max'=>100]),
                        ]),
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
}
