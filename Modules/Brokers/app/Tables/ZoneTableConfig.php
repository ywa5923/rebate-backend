<?php
namespace Modules\Brokers\Tables;

use Modules\Brokers\Models\OptionCategory;
use Modules\Brokers\Models\DropdownCategory;
use Modules\Brokers\Models\BrokerOption;
use App\Tables\TableConfig;
use App\Utilities\ModelHelper;
use Modules\Translations\Models\Country;

final class ZoneTableConfig extends TableConfig
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
            'zone_code' => ['label' => 'Zone Code', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'description' => ['label' => 'Description', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'countries' => ['label' => 'Countries', 'type' => 'text', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'countries_count' => ['label' => 'Countries Count', 'type' => 'number', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'brokers_count' => ['label' => 'Brokers Count', 'type' => 'number', 'visible' => true, 'sortable' => true, 'filterable' => true],
            'created_at' => ['label' => 'Created At', 'type' => 'text', 'visible' => false, 'sortable' => true, 'filterable' => false],
            'updated_at' => ['label' => 'Updated At', 'type' => 'text', 'visible' => false, 'sortable' => true, 'filterable' => false],
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
            ],
            'description' => [
                'type' => 'text', 
                'label' => 'Description',
                'tooltip' => 'Filter by description',
                'placeholder' => 'Search by description'
            ],
            'zone_code' => [
                'type' => 'text', 
                'label' => 'Zone Code',
                'tooltip' => 'Filter by zone code',
                'placeholder' => 'Search by zone code'
            ],
            // 'countries' => [
            //     'type' => 'select', 
            //     'label' => 'Countries',
            //     'tooltip' => 'Filter by countries',
            //     'placeholder' => 'Search by countries',
            //     'options' => ModelHelper::getDistinctOptions(Country::class, 'country_code')
            // ]
        ];
    }

}
