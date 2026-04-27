<?php

namespace Modules\Brokers\Tables;

use App\Tables\TableConfig;

class BrokerGroupTableConfig extends TableConfig
{
    /**
     * Get the table column mapping configuration.
     * Maps server response columns to table configuration with visibility settings.
     */
    public function columns(): array
    {

        return [
            'id' => ['label' => 'ID', 'type' => 'number', 'visible' => true, 'sortable' => true, 'filterable' => false],
            'name' => ['label' => 'Name', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'description' => ['label' => 'Description', 'type' => 'text', 'visible' => true, 'sortable' => false, 'filterable' => true],
            'is_active' => ['label' => 'Is Active', 'type' => 'boolean', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'brokers' => ['label' => 'Brokers', 'type' => 'text', 'visible' => true, 'sortable' => false, 'filterable' => true],

        ];

    }

    public function filters(): array
    {

        return [
            'name' => [
                'type' => 'text',
                'label' => 'Name',
                'tooltip' => 'Filter by name',
                'placeholder' => 'Search by name',
            ],
            'is_active' => [
                'type' => 'select',
                'label' => 'Is Active',
                'tooltip' => 'Filter by active status',
                'options' => [
                    ['value' => 1, 'label' => 'Yes'],
                    ['value' => 0, 'label' => 'No'],
                ],
            ],
            'broker' => [
                'type' => 'text',
                'label' => 'Broker Name',
                'tooltip' => 'Filter by brokers',
                'placeholder' => 'Search by broker name',
            ],
        ];
    }
}
