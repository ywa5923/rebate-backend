<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\ApiAuthController;
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
   # Route::get('/broker-types', [ApiAuthController::class, 'getBrokerTypes']);
    
    //=================================SUPERADMIN ONLY ROUTES======================================
    //TODO: Establish if platform admins can add other platform users like seo,translators,country admins,broker admins,etc.
    Route::middleware(['auth:sanctum', 'superadmin-only'])->get('platform-users/form-config', [PlatformUserController::class, 'getFormConfig']);
    Route::middleware(['auth:sanctum', 'superadmin-only'])->apiResource('platform-users', PlatformUserController::class);
   
    Route::middleware(['auth:sanctum', 'superadmin-only'])->patch('/platform-users/toggle-active-status/{platform_user}', [PlatformUserController::class, 'toggleActiveStatus']);
    
    //=================================PLATFORM ADMIN ONLY ROUTES======================================
    //TODO: Establish if platform admins cand add permissions to seo,translators
    Route::middleware('auth:sanctum')->get('/user-permissions', [UserPermissionController::class, 'index']);
    Route::middleware('auth:sanctum')->delete('/user-permissions/{user_permission}', [UserPermissionController::class, 'destroy']);
    Route::middleware('auth:sanctum')->get('/user-permissions/form-config/{permissionType}', [UserPermissionController::class, 'getFormConfig']);
    Route::middleware('auth:sanctum')->post('/user-permissions/{permissionType}', [UserPermissionController::class, 'store']);
    Route::middleware('auth:sanctum')->patch('/user-permissions/toggle-active-status/{user_permission}', [UserPermissionController::class, 'toggleActiveStatus']);
    
    // Magic link authentication
    Route::post('/login-with-email', [ApiAuthController::class, 'loginWithEmail']);
    Route::post('/magic-link/verify', [ApiAuthController::class, 'verifyMagicLinkToken']);

    
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();
    $userData = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
      
    ];

    if ( $user instanceof \Modules\Auth\Models\BrokerTeamUser) {

        $broker=$user->team->broker;
        $brokerTradingName=$broker->dynamicOptionsValues->where('option_slug', 'trading_name')->where('zone_id', null)->first()->value;

            $userData['user_type'] = 'team_user';

           
            $brokerType=$broker->brokerType->name;
            $brokerCountry=$broker->country->country_code;
            //$brokerZone=$broker->country->zone->name;

            $userData['broker_context'] = [
                'broker_id' => $broker->id,
                //'broker_country' => $user->team->broker->country,
                'broker_name' => $brokerTradingName,
                'broker_type' => $brokerType,
                'broker_country' => $brokerCountry,
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
            $userData['role'] = $user->role;

            
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