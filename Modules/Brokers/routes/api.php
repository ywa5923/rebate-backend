<?php

use Illuminate\Support\Facades\Route;
use Modules\Brokers\Http\Controllers\BrokerController;
use Modules\Brokers\Http\Controllers\BrokerFilterController;
use Modules\Brokers\Http\Controllers\BrokerOptionController;
use Modules\Brokers\Http\Controllers\MatrixController;
use Modules\Brokers\Http\Controllers\AccountTypeController;
use Modules\Brokers\Http\Controllers\CompanyController;
use Modules\Brokers\Http\Controllers\RegulatorController;
use Modules\Brokers\Http\Controllers\OptionValueController;
use Modules\Brokers\Http\Controllers\OptionCategoryController;
use Modules\Brokers\Http\Controllers\UrlController;
use Modules\Brokers\Http\Controllers\PromotionController;
use Modules\Brokers\Http\Controllers\ContestController;
use Modules\Brokers\Http\Controllers\ChallengeController;
use Modules\Brokers\Http\Controllers\ZoneController;
use Modules\Brokers\Http\Controllers\CountryController;
use Modules\Brokers\Http\Controllers\DropdownListController;
use Modules\Auth\Http\Controllers\BrokerTeamUserController;
/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
//     Route::apiResource('brokers', BrokerController::class)->names('brokers');
// });

Route::prefix('v1')->group( function () {

    // Specific routes MUST come before apiResource to avoid conflicts
    Route::middleware('auth:sanctum')->post('/brokers', [BrokerController::class, 'registerBroker']);
    Route::middleware('auth:sanctum')->get('brokers/broker-list/{zone_id?}/{country_id?}', [BrokerController::class, 'getBrokerList']);
    Route::middleware('auth:sanctum')->get('brokers/broker-types-and-countries', [BrokerController::class, 'getBrokerTypesAndCountries']);
    Route::middleware('auth:sanctum')->get('brokers/form-config', [BrokerController::class, 'getFormConfig']);
    Route::middleware('auth:sanctum')->get('brokers/broker-info/{id}', [BrokerController::class, 'getBrokerInfo']);
    Route::middleware('auth:sanctum')->patch('brokers/toggle-active-status/{broker}', [BrokerController::class, 'toggleActiveStatus']);
    Route::get('brokers/{id}', [BrokerController::class, 'show']);
  
   
    Route::get('broker_options', [BrokerOptionController::class, 'index']);
    Route::middleware(['auth:sanctum', 'superadmin-only'])->get('broker-options/get-list', [BrokerOptionController::class, 'getBrokerOptionsList']);
    Route::middleware(['auth:sanctum', 'superadmin-only'])->get('broker-options/form-config', [BrokerOptionController::class, 'getFormConfig']);
    Route::middleware(['auth:sanctum', 'superadmin-only'])->get('broker-options/{id}', [BrokerOptionController::class, 'show']);
    Route::middleware(['auth:sanctum', 'superadmin-only'])->post('broker-options', [BrokerOptionController::class, 'store']);
    Route::middleware(['auth:sanctum', 'superadmin-only'])->put('broker-options/{id}', [BrokerOptionController::class, 'update']);
    Route::middleware(['auth:sanctum', 'superadmin-only'])->delete('broker-options/{id}', [BrokerOptionController::class, 'delete']);
    
    Route::apiResource('broker-filters', BrokerFilterController::class)->names('broker-filters');

    //ROUTES FOR BROKER DASHBOARD
     // OptionValue routes
    // Route::apiResource('option-values', OptionValueController::class)->names('option-values');
     // Multiple option values routes for brokers
     Route::middleware(['auth:sanctum'])->get('option-values/{broker_id}', [OptionValueController::class, 'index']);
     Route::middleware(['auth:sanctum', 'can-admin:Broker,broker_id'])->post('brokers/{broker_id}/option-values', [OptionValueController::class, 'storeMultiple'])->name('option-values.store-multiple');
     Route::middleware(['auth:sanctum', 'can-admin:Broker,broker_id'])->put('brokers/{broker_id}/option-values', [OptionValueController::class, 'updateMultiple'])->name('option-values.update-multiple');
    Route::get('option-categories/get-list', [OptionCategoryController::class, 'getOptionCategoriesList']);
    //this route gets the options categories with their values for a given broker type in broker dashboard
    Route::get('option-categories', [OptionCategoryController::class, 'index']);
   // Route::apiResource('option-categories', OptionCategoryController::class)->names('option-categories');
   
    Route::middleware(['auth:sanctum', 'can-admin:Broker,broker_id'])->get('/matrix/headers/{broker_id}', [MatrixController::class, 'getHeaders']);
    Route::middleware(['auth:sanctum', 'can-admin:Broker,broker_id'])->get('/matrix/{broker_id}', [MatrixController::class, 'index']);
    Route::middleware(['auth:sanctum', 'can-admin:Broker,broker_id'])->post('/matrix/store/{broker_id}', [MatrixController::class, 'store']);
    //Route::post('account-types', [AccountTypeController::class, 'store']);
    Route::get('account-types/{broker_id}', [AccountTypeController::class, 'index']);
   // Route::get('account-types/{id}', [AccountTypeController::class, 'show']);
    Route::middleware(['auth:sanctum', 'can-admin:AccountType,id'])->put('account-types/{id}', [AccountTypeController::class, 'update']);
    Route::middleware(['auth:sanctum', 'can-admin:AccountType,id'])->delete('account-types/{id}', [AccountTypeController::class, 'destroy']);
    Route::get('account-types/{id}/urls', [AccountTypeController::class, 'getUrlsGroupedByType']);
    Route::middleware(['auth:sanctum', 'can-admin:AccountType,id'])->post('account-types/{id?}/urls', [AccountTypeController::class, 'createUrls']);
    Route::middleware(['auth:sanctum', 'can-admin:AccountType,id'])->put('account-types/{id?}/urls', [AccountTypeController::class, 'updateUrls']);
    Route::middleware(['auth:sanctum', 'can-admin:AccountType,accountTypeId'])->delete('account-types/{accountTypeId}/urls/{urlId}', [AccountTypeController::class, 'deleteAccountTypeUrl']);
    Route::middleware(['auth:sanctum', 'can-admin:Broker,broker_id'])->get('urls/{broker_id}/{entity_type}/{entity_id}', [UrlController::class, 'getGroupedUrls']);
   
    //=================================== Company routes =================================
    Route::get('companies/{broker_id}', [CompanyController::class, 'index']);
 
    //=================================== Promotion routes =================================
    Route::get('promotions/{broker_id}', [PromotionController::class, 'index']);
    Route::delete('promotions/{id}', [PromotionController::class, 'destroy']);
    
    //=================================== Contest routes =================================
    Route::get('contests/{broker_id}', [ContestController::class, 'index']);
    Route::delete('contests/{id}', [ContestController::class, 'destroy']);

     //=================================== Challenges table routes =================================
     Route::get('challenges/matrix/headers', [ChallengeController::class, 'getChallengeMatrixHeaders']);
     Route::get('challenges/default-categories', [ChallengeController::class, 'getDefaultChallengeCategories']);       
     Route::get('challenges/categories/{broker_id}', [ChallengeController::class, 'getChallengeCategories']);
    
    
     Route::middleware(['auth:sanctum', 'superadmin-only'])->post('challenges/matrix/placeholders', [ChallengeController::class, 'storeMatrixPlaceholders']);
     Route::middleware(['auth:sanctum', 'can-admin:Broker,broker_id'])->post('challenges/{broker_id}', [ChallengeController::class, 'store']);
    
 
    Route::get('challenges/placeholders', [ChallengeController::class, 'showPlaceholders']);
    Route::get('challenges/{broker_id}', [ChallengeController::class, 'show']);
    Route::post('challenges/{broker_id}/publish', [ChallengeController::class, 'toggleChallengePublish']);
    Route::middleware(['auth:sanctum', 'can-admin:Broker,broker_id'])->delete('challenges/{tab_type}/{broker_id}', [ChallengeController::class, 'removeChallengeTab']);
    Route::middleware(['auth:sanctum', 'can-admin:Broker,broker_id'])->post('challenges/{tab_type}/{broker_id}', [ChallengeController::class, 'addChallengeTab']);
    Route::put('challenges/{broker_id}/tabs/{tab_type}/order', [ChallengeController::class, 'saveChallengeTabOrder']);
   
     
     //=================================SUPERADMIN ONLY ROUTES======================================
     Route::middleware(['auth:sanctum', 'superadmin-only'])->get('zones/form-config', [ZoneController::class, 'getFormConfig']);
     Route::middleware(['auth:sanctum', 'superadmin-only'])->apiResource('zones', ZoneController::class)->names('zones');
     
     // Country REST API routes
     Route::middleware(['auth:sanctum', 'superadmin-only'])->get('countries/form-config', [CountryController::class, 'getFormConfig']);
     Route::middleware(['auth:sanctum', 'superadmin-only'])->apiResource('countries', CountryController::class)->names('countries');

    // Country REST API routes
     Route::middleware(['auth:sanctum', 'superadmin-only'])->get('dropdown-lists/form-config', [DropdownListController::class, 'getFormConfig']);
     Route::middleware(['auth:sanctum', 'superadmin-only'])->apiResource('dropdown-lists', DropdownListController::class)->names('dropdown-lists');
    
});