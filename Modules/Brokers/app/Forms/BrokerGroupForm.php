<?php

namespace Modules\Brokers\Forms;

use App\Forms\Field;
use App\Forms\Form;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;

class BrokerGroupForm extends Form
{
    public function getFormData(): array
    {
        return [
            'name' => 'Broker Group',
            'description' => 'Broker group form configuration',
            'sections' => [
                'definitions' => [
                    'label' => 'Broker Group Definitions',
                    'fields' => [
                        //'broker_type' => Field::select('Broker Type', $this->getBrokerTypes(),['required'=>true,'exists'=>'broker_types,id']),
                        // 'broker_type_id' => Field::select('Broker Type', $this->getOptionsList(BrokerType::class, 'name'), ['required' => true, 'exists' => 'broker_types,id']),

                        'name' => Field::text('Group Name', ['required' => true, 'min' => 3, 'max' => 100]),

                        'description' => Field::text('Group Description', ['required' => true, 'min' => 3, 'max' => 100]),

                        'is_active' => Field::select('Is Active', $this->booleanOptions(), ['required' => true]),

                        'brokers' => Field::multiselect('Brokers', '/broker-groups/search-by-broker-trading-name', 'trading_name', null, ['required' => true]),
                        //'brokers' => Field::multiselect('Brokers', null, null, $this->getBrokersList(), ['required' => true]),
                    ],
                ],
            ],
        ];
    }

    private function booleanOptions(): array
    {
        return [
            ['value' => 1, 'label' => 'Yes'],
            ['value' => 0, 'label' => 'No'],
        ];
    }

    public function getBrokersList(): array
    {
        return Broker::query()
            ->leftJoin('option_values', function ($join) {
                $join->on('option_values.broker_id', '=', 'brokers.id')
                    ->where('option_values.option_slug', '=', 'trading_name')
                    ->whereNull('option_values.zone_code');
            })
            ->selectRaw('brokers.id as value, option_values.value as label')
            ->orderBy('option_values.value', 'asc')
            ->get()
            ->map(fn ($broker) => [
                'value' => $broker->value,
                'label' => (string) ($broker->label ?? $broker->value),
            ])
            ->values()
            ->all();
    }
}
