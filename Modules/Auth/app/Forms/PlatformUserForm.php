<?php


namespace Modules\Auth\Forms;

use App\Forms\Form;
use App\Forms\Field;

use Modules\Auth\Models\PlatformUser;
use Modules\Auth\Enums\AuthRole;
class PlatformUserForm extends Form
{
    
    public function getFormData(): array
    {
        return [
            'name' => 'Broker Option',
            'description' => 'Broker option form configuration',
            'sections' => [
                'definitions' => [
                    'label' => 'Platform User Definitions',
                    'fields' => [
                        //'broker_type' => Field::select('Broker Type', $this->getBrokerTypes(),['required'=>true,'exists'=>'broker_types,id']),
                        'name' => Field::text('Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'email' => Field::text('Email', ['required'=>true, 'min'=>3, 'max'=>100, 'email','unique'=>'platform_users,email']),
                        'role' => Field::select('Role', $this->getRoles(),['required'=>true]),
                        'is_active' => Field::select('Is Active', $this->booleanOptions(),['required'=>true]),
                    ]
                    
                    ]
                
            ]
        ];
    }

    private function booleanOptions(): array
    {
        return [
            ['value' => 1, 'label' => 'Yes'],
            ['value' => 0, 'label' => 'No'],
        ];
    }

    private function getRoles(): array
    {
        // return [
        //     ['value' => 'super-admin', 'label' => 'Super Admin'],
        //     ['value' => 'country_admin', 'label' => 'Country Admin'],
        //     ['value' => 'broker_admin', 'label' => 'Broker Admin'],
        //     ['value' => 'seo', 'label' => 'SEO'],
        //     ['value' => 'translator', 'label' => 'Translator'],
        // ];
       
            $roles = AuthRole::cases();
            return array_map(function ($role) {
                return ['value' => $role->value, 'label' => ucfirst($role->value.' Role')];
            }, $roles);
    
            
        
    }

    // private function getBrokerTypes(): array
    // {
    //     return BrokerType::all()
    //         ->map(function ($type) {
    //             return ['value' => $type->id, 'label' => $type->name];
    //         })
    //         ->values()
    //         ->all();
    // }

    // private function getCountries(): array
    // {
    //     return Country::all()
    //         ->map(function ($country) {
    //             return ['value' => $country->id, 'label' => $country->name];
    //         })
    //         ->values()
    //         ->all();
    // }
}


