<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthenticatedSessionController;
use Modules\Auth\Http\Controllers\ApiAuthController;
use Modules\Auth\Http\Controllers\EmailVerificationNotificationController;
use Modules\Auth\Http\Controllers\NewPasswordController;
use Modules\Auth\Http\Controllers\PasswordResetLinkController;
use Modules\Auth\Http\Controllers\RegisteredUserController;
use Modules\Auth\Http\Controllers\VerifyEmailController;
use Modules\Auth\Http\Controllers\BrokerTeamUserController;
use Modules\Auth\Http\Controllers\PlatformUserController;
use Modules\Auth\Http\Controllers\UserPermissionController;

// Broker registration routes
Route::group([], function () {
    // Broker registration by admin
    //Route::post('/register-broker', [BrokerTeamUserController::class, 'registerBroker']);
   
    Route::post('/broker-team-user', [BrokerTeamUserController::class, 'registerUserToBrokerDefaultTeam']);
    Route::put('/broker-team-user/{userId}', [BrokerTeamUserController::class, 'updateBrokerTeamUser']);
    Route::delete('/broker-team-user/{userId}', [BrokerTeamUserController::class, 'deleteBrokerTeamUser']);
    Route::get('/broker-default-team/{brokerId}', [BrokerTeamUserController::class, 'getBrokerDefaultTeam']);
    
    // Get available broker types
    Route::get('/broker-types', [ApiAuthController::class, 'getBrokerTypes']);
    
    // Platform users CRUD
    Route::apiResource('platform-users', PlatformUserController::class);
    Route::patch('/platform-users/{platform_user}/toggle', [PlatformUserController::class, 'toggleActiveStatus']);
    
    // User permissions CRUD
    Route::apiResource('user-permissions', UserPermissionController::class);
    Route::patch('/user-permissions/{user_permission}/toggle', [UserPermissionController::class, 'toggleActiveStatus']);
    
    // Magic link authentication
   // Route::post('/magic-link/send', [ApiAuthController::class, 'sendMagicLink']);
    Route::post('/login-with-email', [ApiAuthController::class, 'loginWithEmail']);
    Route::post('/magic-link/verify', [ApiAuthController::class, 'verifyMagicLinkToken']);

    
   
    
    // Platform user magic link authentication
    Route::post('/platform-user/magic-link/send', [ApiAuthController::class, 'sendPlatformUserMagicLink']);
    
   
    
    // Team management
    Route::post('/teams', [ApiAuthController::class, 'createTeam']);
    Route::get('/teams', [ApiAuthController::class, 'getBrokerTeams']);
    Route::post('/team-users', [ApiAuthController::class, 'createTeamUser']);
    Route::get('/team-users', [ApiAuthController::class, 'getTeamUsers']);
    Route::post('/team-users/magic-link', [ApiAuthController::class, 'sendTeamUserMagicLink']);
    Route::get('/team-roles-permissions', [ApiAuthController::class, 'getTeamRolesAndPermissions']);
    
    // Resource permission management
    Route::post('/resource-permissions', [ApiAuthController::class, 'createResourcePermission']);
    Route::get('/team-users/{teamUserId}/resource-permissions', [ApiAuthController::class, 'getResourcePermissions']);
    Route::put('/resource-permissions/{permissionId}', [ApiAuthController::class, 'updateResourcePermission']);
    Route::delete('/resource-permissions/{permissionId}', [ApiAuthController::class, 'deleteResourcePermission']);
    Route::patch('/resource-permissions/{permissionId}/toggle', [ApiAuthController::class, 'togglePermissionActive']);
    Route::get('/team-users/{teamUserId}/permission-stats', [ApiAuthController::class, 'getPermissionStats']);
    
    // Specific permission assignments
    Route::post('/assign-broker-permission', [ApiAuthController::class, 'assignBrokerPermission']);
    Route::post('/assign-country-permission', [ApiAuthController::class, 'assignCountryPermission']);
    Route::post('/assign-zone-permission', [ApiAuthController::class, 'assignZonePermission']);
    
    // Permission options
    Route::get('/permission-options', [ApiAuthController::class, 'getPermissionOptions']);
    
    // Super Admin functions (should be protected with super admin middleware)
    Route::prefix('super-admin')->group(function () {
        // Country management
        Route::post('/assign-country-management', [ApiAuthController::class, 'assignCountryManagement']);
        Route::post('/revoke-country-management', [ApiAuthController::class, 'revokeCountryManagement']);
        Route::post('/bulk-assign-country-management', [ApiAuthController::class, 'bulkAssignCountryManagement']);
        
        // Zone management
        Route::post('/assign-zone-management', [ApiAuthController::class, 'assignZoneManagement']);
        
        // Global broker management
        Route::post('/assign-global-broker-management', [ApiAuthController::class, 'assignGlobalBrokerManagement']);
        
        // Management queries
        Route::get('/team-users/{teamUserId}/manageable-brokers', [ApiAuthController::class, 'getManageableBrokers']);
        Route::get('/team-users/{teamUserId}/management-stats', [ApiAuthController::class, 'getManagementStats']);
    });
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();
    $userData = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
      
    ];

    if ( $user instanceof \Modules\Auth\Models\BrokerTeamUser) {
        $brokerTradingName=$user->team->broker->dynamicOptionsValues->where('option_slug', 'trading_name')->where('zone_id', null)->first()->value;

            $userData['user_type'] = 'team_user';
            $userData['broker_context'] = [
                'broker_id' => $user->team->broker_id,
                //'broker_country' => $user->team->broker->country,
                'broker_name' => $brokerTradingName,
                'team_id' => $user->broker_team_id,
                'team_name' => $user->team->name,
            ];
            //$userData['role'] = $subject->role;
            $userData['permissions'] = $user->resourcePermissions->map(function($permission) {
                return [
                    'type' => $permission->permission_type,
                    'resource_id' => $permission->resource_id,
                    'resource_value' => $permission->resource_value,
                    'action' => $permission->action,
                ];
            })->toArray();
            } elseif ( $user instanceof \Modules\Auth\Models\PlatformUser) {
            $userData['user_type'] = 'platform_user';
            //$userData['role'] = $user->role;

            
            $userData['permissions'] = $user->resourcePermissions->map(function($permission) {
                return [
                    'type' => $permission->permission_type,
                    'resource_id' => $permission->resource_id,
                    'resource_value' => $permission->resource_value,
                    'action' => $permission->action,
                ];
            })->toArray();
        }
        return response()->json([
            'success' => true,
            'user' => $userData,
        ]);
});