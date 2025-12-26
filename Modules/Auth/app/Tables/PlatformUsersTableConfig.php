<?php


namespace Modules\Auth\Tables;

use App\Tables\TableConfig;
final class PlatformUsersTableConfig extends TableConfig
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
           
            'name' => ['label' => 'Name', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'email' => ['label' => 'Email', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'role' => ['label' => 'Role', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'last_login_at' => ['label' => 'Last Login At', 'type' => 'text', 'visible' => true, 'sortable' => true],
            'is_active' => ['label' => 'Is Active', 'type' => 'boolean', 'visible' => true, 'sortable' => true],
            'created_at' => ['label' => 'Created At', 'type' => 'text', 'visible' => false, 'sortable' => true],
            'updated_at' => ['label' => 'Updated At', 'type' => 'text', 'visible' => false, 'sortable' => true],
            
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
            'email' => [
                'type' => 'text', 
                'label' => 'Email',
                'tooltip' => 'Filter by email',
                'placeholder' => 'Search by email'
                ]
                ,
            'role' => [
                'type' => 'text', 
                'label' => 'Role',
                'tooltip' => 'Filter by role',
                'placeholder' => 'Search by role'
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
            
                
        ];
    }

    

   
}

