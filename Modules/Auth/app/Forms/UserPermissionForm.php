<?php



namespace Modules\Auth\Forms;

use App\Forms\Form;
use App\Forms\Field;

use Modules\Auth\Models\PlatformUser;
class UserPermissionForm extends Form
{
    public function getFormData(): array
    {
        return [
            'name' => 'User Permission',
            'description' => 'User permission form configuration',
            'sections' => [
                'definitions' => [
                    'label' => 'User Permission Definitions',
                    'fields' => [
                        //'broker_type' => Field::select('Broker Type', $this->getBrokerTypes(),['required'=>true,'exists'=>'broker_types,id']),
                        'subject_type' => Field::select('Subject Type', $this->getSubjectTypes(),['required'=>true]),
                        'subject_id' => Field::text('Subject ID', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'permission_type' => Field::select('Permission Type', $this->getPermissionTypes(),['required'=>true]),
                        'resource_id' => Field::text('Resource ID', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'resource_value' => Field::text('Resource Value', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'action' => Field::select('Action', $this->getActions(),['required'=>true]),
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

    private function getSubjectTypes(): array
    {
        return [
            ['value' => 'PlatformUser', 'label' => 'Platform User'],
            ['value' => 'BrokerTeamUser', 'label' => 'Broker Team User'],
        ];
    }

    private function getPermissionTypes(): array
    {
        return [
            ['value' => 'broker', 'label' => 'Broker'],
            ['value' => 'country', 'label' => 'Country'],
            ['value' => 'zone', 'label' => 'Zone'],
            ['value' => 'seo', 'label' => 'SEO'],
            ['value' => 'translator', 'label' => 'Translator'],
        ];
    }

    private function getActions(): array
    {
        return [
            ['value' => 'view', 'label' => 'View'],
            ['value' => 'edit', 'label' => 'Edit'],
            ['value' => 'delete', 'label' => 'Delete'],
            ['value' => 'manage', 'label' => 'Manage'],
        ];
    }
}


