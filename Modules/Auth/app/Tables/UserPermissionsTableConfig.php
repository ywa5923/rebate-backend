<?php



namespace Modules\Auth\Tables;

use App\Tables\TableConfig;
final class UserPermissionsTableConfig extends TableConfig
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
            'subject_type' => ['label' => 'User Type', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'subject_id' => ['label' => 'User id', 'type' => 'number', 'visible' => true, 'sortable' => true],
            'subject' => ['label' => 'User Info', 'type' => 'text', 'visible' => true, 'sortable' => false],
            'permission_type' => ['label' => 'Permission Type', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'resource_id' => ['label' => 'Resource ID', 'type' => 'number', 'visible' => true, 'sortable' => true],
            'resource_value' => ['label' => 'Resource Value', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'action' => ['label' => 'Action', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'is_active' => ['label' => 'Is Active', 'type' => 'boolean', 'visible' => true, 'sortable' => true],
            'created_at' => ['label' => 'Created At', 'type' => 'text', 'visible' => false, 'sortable' => true],
            'updated_at' => ['label' => 'Updated At', 'type' => 'text', 'visible' => false, 'sortable' => true],
        ];
    }
    public function filters(): array
    {

        
        return [
            'subject_type' => [
                'type' => 'select', 
                'label' => 'User Type',
                'tooltip' => 'Filter by user type',
                'placeholder' => 'Search by user type',
                'options' => [
                    ['value' => 'PlatformUser', 'label' => 'Platform User'],
                    ['value' => 'BrokerTeamUser', 'label' => 'Broker Team User'],
                ]
                ]
                ,
            'subject_id' => [
                'type' => 'text', 
                'label' => 'User ID',
                'tooltip' => 'Filter by user ID',
                'placeholder' => 'Search by user ID'
                ]
                ,
            'permission_type' => [
                'type' => 'select', 
                'label' => 'Permission Type',
                'tooltip' => 'Filter by permission type',
                'placeholder' => 'Search by permission type',
                'options' => [
                    ['value' => 'broker', 'label' => 'Broker'],
                    ['value' => 'country', 'label' => 'Country'],
                    ['value' => 'zone', 'label' => 'Zone'],
                    ['value' => 'seo', 'label' => 'SEO'],
                    ['value' => 'translator', 'label' => 'Translator'],
                ]
                ],
             'action'=>[
                'type' => 'select', 
                'label' => 'Action',
                'tooltip' => 'Filter by action',
                'options' => [
                    ['value' => 'view', 'label' => 'View'],
                    ['value' => 'edit', 'label' => 'Edit'],
                    ['value' => 'delete', 'label' => 'Delete'],
                    ['value' => 'manage', 'label' => 'Manage'],
                ]
             ],  
             'resource_id'=>[
                'type' => 'number', 
                'label' => 'Resource ID',
                'tooltip' => 'Filter by resource ID',
                'placeholder' => 'Search by resource ID'
             ],
             'resource_value'=>[
                'type' => 'text', 
                'label' => 'Resource Value',
                'tooltip' => 'Filter by resource value',
                'placeholder' => 'Search by resource value'
             ],
             'subject'=>[
                'type' => 'text', 
                'label' => 'User Info',
                'tooltip' => 'Filter by user name or email',
                'placeholder' => 'Search by user info'
             ],
            'is_active' => [
                'type' => 'select', 
                'label' => 'Is Active',
                'tooltip' => 'Filter by active status',
                'options' => [
                    ['value' => 1, 'label' => 'Yes'],
                    ['value' => 0, 'label' => 'No'],
                ]
            ],
            
                
        ];
    }

    

   
}

