<?php

namespace Modules\Brokers\Tables;

use Modules\Brokers\Models\Zone;
use App\Tables\TableConfig;
use App\Utilities\ModelHelper;
final class CountryTableConfig extends TableConfig
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
            'id' => ['label' => 'ID', 'type' => 'number', 'visible' => true, 'sortable' => true, 'filterable' => false],
            'name' => ['label' => 'Name', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'country_code' => ['label' => 'Country Code', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'zone_name' => ['label' => 'Zone Name', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'zone_code' => ['label' => 'Zone Code', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'brokers_count' => ['label' => 'Brokers Count', 'type' => 'number', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'created_at' => ['label' => 'Created At', 'type' => 'text', 'visible' => false, 'sortable' => true, 'filterable' => false],
            
        ];
    }
    public function filters(): array
    {

        
        return [
            'name' => [
                'type' => 'text', 
                'label' => 'Name',
                'tooltip' => 'Filter by name',
                'placeholder' => 'Search by name'
                ]
                ,
            'country_code' => [
                'type' => 'text', 
                'label' => 'Country Code',
                'tooltip' => 'Filter by country code',
                'placeholder' => 'Search by country code'
                ]
                ,
            'zone_code' => [
                'type' => 'select', 
                'label' => 'Zone Code',
                'tooltip' => 'Filter by zone code',
                //'placeholder' => 'Search by zone code',
                'options' => ModelHelper::getDistinctOptions(Zone::class, 'zone_code')
                
            ],
            
                
        ];
    }

    

   
}
