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
    Route::apiResource('brokers', BrokerController::class)->names('brokers');
    Route::apiResource('broker_options', BrokerOptionController::class)->names('broker_options');
    Route::apiResource('broker-filters', BrokerFilterController::class)->names('broker-filters');

    Route::apiResource('option-categories', OptionCategoryController::class)->names('option-categories');
    Route::get('/matrix/headers', [MatrixController::class, 'getHeaders']);
    Route::get('/matrix', [MatrixController::class, 'index']);
    Route::post('/matrix/store', [MatrixController::class, 'store']);
    Route::post('account-types', [AccountTypeController::class, 'store']);
    Route::get('account-types', [AccountTypeController::class, 'index']);
    Route::get('account-types/{id}', [AccountTypeController::class, 'show']);
    Route::put('account-types/{id}', [AccountTypeController::class, 'update']);
    Route::delete('account-types/{id}', [AccountTypeController::class, 'destroy']);
    Route::get('account-types/{id}/urls', [AccountTypeController::class, 'getUrlsGroupedByType']);
    Route::post('account-types/{id}/urls', [AccountTypeController::class, 'createUrls']);
    Route::put('account-types/{id}/urls', [AccountTypeController::class, 'updateUrls']);
    Route::delete('account-types/{accountTypeId}/urls/{urlId}', [AccountTypeController::class, 'deleteUrl']);
    Route::get('urls/{broker_id}/{entity_type}/{entity_id}', [UrlController::class, 'getGroupedUrls']);
    // Company routes
    Route::apiResource('companies', CompanyController::class)->names('companies');
    Route::apiResource('regulators', RegulatorController::class)->names('regulators');
    
    // OptionValue routes
    Route::apiResource('option-values', OptionValueController::class)->names('option-values');
    
    // Multiple option values routes for brokers
    Route::post('brokers/{broker_id}/option-values', [OptionValueController::class, 'storeMultiple'])->name('option-values.store-multiple');
    Route::put('brokers/{broker_id}/option-values', [OptionValueController::class, 'updateMultiple'])->name('option-values.update-multiple');
});