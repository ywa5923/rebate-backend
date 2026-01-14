<?php


namespace Modules\Brokers\Forms;

use App\Forms\Form;
use App\Forms\Field;

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
                        'country_code' => Field::text('Country Code', ['required'=>true, 'min'=>2, 'max'=>10,'unique'=>'countries,country_code']),
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
