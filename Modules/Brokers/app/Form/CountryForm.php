<?php


namespace Modules\Brokers\Form;

use App\Form\Form;
use App\Form\Field;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\OptionCategory;
use Modules\Brokers\Models\DropdownCategory;
use Modules\Brokers\Models\Zone;
class CountryForm extends Form
{
    public function getFormData(): array
    {
        return [
            'name' => 'Broker Option',
            'description' => 'Broker option form configuration',
            'sections' => [
                'definitions' => [
                    'label' => 'CountryDefinitions',
                    'fields' => [
                        'name' => Field::text('Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'country_code' => Field::text('Slug', ['required'=>true, 'min'=>2, 'max'=>10]),
                        'zone_id' => Field::select('Zone', $this->getZones(),['required'=>true,'exists'=>'zones,id']),
                    ]
                    
                    ]
                
                
            ]
        ];
    }

    private function getZones(): array
    {
        return Zone::all()
            ->map(function ($zone) {
                return ['value' => $zone->id, 'label' => $zone->name];
            })
            ->values()
            ->all();
    }

}
