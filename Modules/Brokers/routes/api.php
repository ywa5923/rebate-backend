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

Route::group(["prefix"=>'v1'], function () {
    // Specific routes MUST come before apiResource to avoid conflicts
    Route::get('brokers/broker-list', [BrokerController::class, 'getBrokerList']);
    Route::get('brokers/broker-types-and-countries', [BrokerController::class, 'getBrokerTypesAndCountries']);
    Route::get('brokers/broker-info/{id}', [BrokerController::class, 'getBrokerInfo']);
    Route::patch('brokers/toggle-active-status/{id}', [BrokerController::class, 'toggleActiveStatus']);
    Route::get('brokers/{id}', [BrokerController::class, 'show']);
  
   // Route::apiResource('brokers', BrokerController::class)->names('brokers');
    
    //Route::apiResource('broker_options', BrokerOptionController::class)->names('broker_options');
    Route::get('broker_options', [BrokerOptionController::class, 'index']);
    Route::get('broker-options/get-list', [BrokerOptionController::class, 'getBrokerOptionsList']);
    //Route::get('broker-options/form-meta-data', [BrokerOptionController::class, 'getFormMetaData']);
    Route::get('broker-options/form-config', [BrokerOptionController::class, 'getFormConfig']);
    Route::get('broker-options/{id}', [BrokerOptionController::class, 'show']);
    Route::post('broker-options', [BrokerOptionController::class, 'store']);
    Route::put('broker-options/{id}', [BrokerOptionController::class, 'update']);
    Route::delete('broker-options/{id}', [BrokerOptionController::class, 'delete']);
    
    Route::apiResource('broker-filters', BrokerFilterController::class)->names('broker-filters');

    // Option Category routes - specific routes must come before apiResource
    Route::get('option-categories/get-list', [OptionCategoryController::class, 'getOptionCategoriesList']);
    Route::apiResource('option-categories', OptionCategoryController::class)->names('option-categories');
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
    // Company routes
    Route::apiResource('companies', CompanyController::class)->names('companies');
    Route::apiResource('regulators', RegulatorController::class)->names('regulators');
    
    // OptionValue routes
    Route::apiResource('option-values', OptionValueController::class)->names('option-values');
    
    // Multiple option values routes for brokers
    Route::post('brokers/{broker_id}/option-values', [OptionValueController::class, 'storeMultiple'])->name('option-values.store-multiple');
    Route::put('brokers/{broker_id}/option-values', [OptionValueController::class, 'updateMultiple'])->name('option-values.update-multiple');
    
    // Promotion routes
    Route::get('promotions', [PromotionController::class, 'index']);
    Route::delete('promotions/{id}', [PromotionController::class, 'destroy']);
    
    // Contest routes
    Route::get('contests', [ContestController::class, 'index']);
    Route::delete('contests/{id}', [ContestController::class, 'destroy']);

     // Challenges table routes
     Route::get('challenges/categories', [ChallengeController::class, 'getChallengeCategories']);
     Route::post('challenges', [ChallengeController::class, 'store']);
    // Route::get('challenges', [ChallengeController::class, 'index']);
    Route::get('challenges', [ChallengeController::class, 'show']);
     Route::get('challenges/show', [ChallengeController::class, 'show']);
     
     // Zone REST API routes
     Route::get('zones/form-config', [ZoneController::class, 'getFormConfig']);
     Route::apiResource('zones', ZoneController::class)->names('zones');
     
     // Country REST API routes
     Route::get('countries/form-config', [CountryController::class, 'getFormConfig']);
     Route::apiResource('countries', CountryController::class)->names('countries');
     
     // Dropdown categories REST API routes
     Route::get('dropdown-list', [DropdownListController::class, 'index']);
     Route::get('dropdown-list/{id}', [DropdownListController::class, 'showList']);
     Route::delete('dropdown-list/{id}', [DropdownListController::class, 'deleteList']);
     Route::post('dropdown-list/store-list', [DropdownListController::class, 'storeList']);
     Route::put('dropdown-list/update-list/{id}', [DropdownListController::class, 'updateList']);

});