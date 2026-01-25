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


    Route::middleware(['auth:sanctum'])->group(function () {

       
      
    });

      
       
    // Specific routes MUST come before apiResource to avoid conflicts
    Route::middleware('auth:sanctum')->post('/brokers', [BrokerTeamUserController::class, 'registerBroker']);
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
    Route::get('option-categories', [OptionCategoryController::class, 'index']);
   // Route::apiResource('option-categories', OptionCategoryController::class)->names('option-categories');
   
    Route::get('/matrix/headers', [MatrixController::class, 'getHeaders']);
    Route::get('/matrix', [MatrixController::class, 'index']);
    Route::post('/matrix/store', [MatrixController::class, 'store']);
    //Route::post('account-types', [AccountTypeController::class, 'store']);
    Route::get('account-types', [AccountTypeController::class, 'index']);
    Route::get('account-types/{id}', [AccountTypeController::class, 'show']);
    Route::put('account-types/{id}', [AccountTypeController::class, 'update']);
    Route::delete('account-types/{id}', [AccountTypeController::class, 'destroy']);
    Route::get('account-types/{id}/urls', [AccountTypeController::class, 'getUrlsGroupedByType']);
    Route::post('account-types/{id?}/urls', [AccountTypeController::class, 'createUrls']);
    Route::put('account-types/{id?}/urls', [AccountTypeController::class, 'updateUrls']);
    Route::delete('account-types/{accountTypeId}/urls/{urlId}', [AccountTypeController::class, 'deleteAccountTypeUrl']);
    Route::get('urls/{broker_id}/{entity_type}/{entity_id}', [UrlController::class, 'getGroupedUrls']);
   
    //=============== Company routes =======================
   // Route::apiResource('companies', CompanyController::class)->names('companies');
    Route::get('companies/{broker_id}', [CompanyController::class, 'index']);
 
    //Route::apiResource('regulators', RegulatorController::class)->names('regulators');
    
    // Promotion routes
    Route::get('promotions/{broker_id}', [PromotionController::class, 'index']);
    Route::delete('promotions/{id}', [PromotionController::class, 'destroy']);
    
    // Contest routes
    Route::get('contests/{broker_id}', [ContestController::class, 'index']);
    Route::delete('contests/{id}', [ContestController::class, 'destroy']);

     // Challenges table routes
     Route::get('challenges/categories', [ChallengeController::class, 'getChallengeCategories']);
     Route::post('challenges', [ChallengeController::class, 'store']);
    // Route::get('challenges', [ChallengeController::class, 'index']);
    Route::get('challenges', [ChallengeController::class, 'show']);
     Route::get('challenges/show', [ChallengeController::class, 'show']);
     
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