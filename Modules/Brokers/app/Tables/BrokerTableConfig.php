<?php


namespace Modules\Brokers\Tables;

use App\Tables\TableConfig;
final class BrokerTableConfig extends TableConfig
{
    /**
     * Get the table column mapping configuration.
     * Maps server response columns to table configuration with visibility settings.
     *
     * @return array
     */
    public  function columns(): array
    {
       
        return [
            'id' => ['label' => 'ID', 'type' => 'number', 'visible' => true, 'sortable' => true],
            'trading_name' => ['label' => 'Trading Name', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'broker_type' => ['label' => 'Broker Type', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'is_active' => ['label' => 'Is Active', 'type' => 'boolean', 'visible' => true, 'sortable' => true],
            'country_id' => ['label' => 'Country ID', 'type' => 'number', 'visible' => false, 'sortable' => true],
            'zone_id' => ['label' => 'Zone ID', 'type' => 'number', 'visible' => false, 'sortable' => true],
            'country_code' => ['label' => 'Country Code', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'zone_code' => ['label' => 'Zone Code', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'logo' => ['label' => 'Logo', 'type' => 'text', 'visible' => true, 'sortable' => false],
            'home_url' => ['label' => 'Home URL', 'type' => 'text', 'visible' => true, 'sortable' => false],
            'created_at' => ['label' => 'Created At', 'type' => 'text', 'visible' => false, 'sortable' => true],
            'updated_at' => ['label' => 'Updated At', 'type' => 'text', 'visible' => false, 'sortable' => true],
            
        ];
    }
    public function filters(): array
    {

        
        return [
            'trading_name' => [
                'type' => 'text', 
                'label' => 'Trading Name',
                'tooltip' => 'Filter by trading name',
                'placeholder' => 'Search by trading name'
                ]
                ,
            'broker_type' => [
                'type' => 'text', 
                'label' => 'Broker Type',
                'tooltip' => 'Filter by broker type',
                'placeholder' => 'Search by broker type'
                ]
                ,
            'is_active' => [
                'type' => 'select', 
                'label' => 'Is Active',
                'tooltip' => 'Filter by active status',
                'options' => [
                    ['value' => 1, 'label' => 'Yes'],
                    ['value' => 0, 'label' => 'No'],
                ]
            ],
            'country' => [
                'type' => 'text', 
                'label' => 'Country Code',
                'tooltip' => 'Filter by country code',
                'placeholder' => 'Search by country code'
            ],
            'zone' => [
                'type' => 'text', 
                'label' => 'Zone Code',
                'tooltip' => 'Filter by zone code',
                'placeholder' => 'Search by zone code'
            ],
            
                
        ];
    }

    

   
}
