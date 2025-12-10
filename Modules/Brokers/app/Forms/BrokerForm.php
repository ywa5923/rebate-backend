<?php


namespace Modules\Brokers\Forms;

use App\Forms\Form;
use App\Forms\Field;

use Modules\Brokers\Models\Zone;
use Modules\Brokers\Models\BrokerType;
use Modules\Translations\Models\Country;
class BrokerForm extends Form
{
    public function getFormData(): array
    {
        return [
            'name' => 'Broker Option',
            'description' => 'Broker option form configuration',
            'sections' => [
                'definitions' => [
                    'label' => 'Broker Definitions',
                    'fields' => [
                        'broker_type' => Field::select('Broker Type', $this->getBrokerTypes(),['required'=>true,'exists'=>'broker_types,id']),
                        'trading_name' => Field::text('Trading Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'email' => Field::text('Email', ['required'=>true, 'min'=>3, 'max'=>100]),
                        
                        //'is_active' => Field::select('Is Active', $this->booleanOptions(),['required'=>true]),
                        'country_id' => Field::select('Country', $this->getCountries(),['required'=>true,'exists'=>'countries,id']),
                        
                        
                       
                    ]
                    
                    ]
                
                
            ]
        ];
    }

    private function getBrokerTypes(): array
    {
        return BrokerType::all()
            ->map(function ($type) {
                return ['value' => $type->id, 'label' => $type->name];
            })
            ->values()
            ->all();
    }

    private function getCountries(): array
    {
        return Country::all()
            ->map(function ($country) {
                return ['value' => $country->id, 'label' => $country->name];
            })
            ->values()
            ->all();
    }
}

