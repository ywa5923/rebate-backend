<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\PlatformUser;
use Modules\Auth\Models\UserPermission;
use Modules\Auth\Enums\AuthPermission;
use Modules\Auth\Enums\AuthAction;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = PlatformUser::create([
            'name' => 'Sergiu',
            'email' => 'sergiu@financialtradingart.com',
            'role' => 'super-admin',
            'is_active' => true,
        ]);
        UserPermission::create([
            'subject_type'    => PlatformUser::class,            
            'subject_id'      => $user1->id,                      
            'permission_type' => 'super-admin',
            'resource_id'     => null,                            
            'action'          => AuthAction::MANAGE->value,      
            'is_active'       => true,
        ]);
        $user2 = PlatformUser::create([
            'name' => 'Ion Ivan',
            'email' => 'felix@websynergy.ro',
            'role' => 'super-admin',
            'is_active' => true,
        ]);
        UserPermission::create([
            'subject_type'    => PlatformUser::class,            
            'subject_id'      => $user2->id,                      
            'permission_type' => 'super-admin',
            'resource_id'     => null,                            
            'action'          => AuthAction::MANAGE->value,      
            'is_active'       => true,
        ]);

        $user3 = PlatformUser::create([
            'name' => 'Ion Ivan',
            'email' => 'ionivan1043@gmail.com',
            'role' => 'super-admin',
            'is_active' => true,
        ]);
        UserPermission::create([
            'subject_type'    => PlatformUser::class,            
            'subject_id'      => $user3->id,                      
            'permission_type' => 'super-admin',
            'resource_id'     => null,                            
            'action'          => AuthAction::MANAGE->value,      
            'is_active'       => true,
        ]);
    }
}
