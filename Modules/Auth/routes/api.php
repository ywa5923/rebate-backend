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

// Broker registration routes
Route::group([], function () {
    // Broker registration by admin
    Route::post('/register-broker', [ApiAuthController::class, 'registerBroker']);
    
    // Get available broker types
    Route::get('/broker-types', [ApiAuthController::class, 'getBrokerTypes']);
    
    // Magic link authentication
   // Route::post('/magic-link/send', [ApiAuthController::class, 'sendMagicLink']);
   // Route::post('/magic-link/verify', [ApiAuthController::class, 'verifyMagicLink']);
    Route::post('/magic-link/verify-token', [ApiAuthController::class, 'verifyMagicLinkToken']);
    Route::post('/magic-link/decode-token', [ApiAuthController::class, 'decodeToken']);
    Route::post('/magic-link/revoke', [ApiAuthController::class, 'revokeBrokerTokens']);
    Route::post('/platform-user/revoke-tokens', [ApiAuthController::class, 'revokePlatformUserTokens']);
    Route::get('/magic-link/stats', [ApiAuthController::class, 'getMagicLinkStats']);
    
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
    return $request->user()->load('roles');
});