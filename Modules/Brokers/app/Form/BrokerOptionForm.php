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
                        'name' => Field::text('Name', ['required', 'min:3', 'max:100']),
                        'slug' => Field::text('Slug', ['required']),
                    ],
                ],
                'applicability' => [
                    'label' => 'Applicability',
                    'fields' => [
                        'applicable_for' => Field::select(
                            'Applicable For',
                            $this->getDistinctOptions(BrokerOption::class, 'applicable_for'),
                            ['required']
                        ),
                        'for_brokers' => Field::select('For Brokers', $this->booleanOptions(), ['required']),
                        'for_props'   => Field::select('For Props', $this->booleanOptions(), ['required']),
                        'for_crypto'  => Field::select('For Crypto', $this->booleanOptions(), ['required']),
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
