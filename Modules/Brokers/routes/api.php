<?php

use Illuminate\Support\Facades\Route;
use Modules\Brokers\Http\Controllers\BrokerController;
use Modules\Brokers\Http\Controllers\BrokerFilterController;
use Modules\Brokers\Http\Controllers\BrokerOptionController;
use Modules\Brokers\Http\Controllers\MatrixController;
use Modules\Brokers\Http\Controllers\AcountTypeController;
use Modules\Brokers\Http\Controllers\CompanyController;
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
    Route::get('/matrix/headers', [MatrixController::class, 'getHeaders']);
    Route::get('/matrix', [MatrixController::class, 'index']);
    Route::post('/matrix/store', [MatrixController::class, 'store']);
    Route::post('account-types', [AcountTypeController::class, 'store']);
    Route::get('account-types', [AcountTypeController::class, 'index']);
    Route::get('account-types/{id}', [AcountTypeController::class, 'show']);
    Route::put('account-types/{id}', [AcountTypeController::class, 'update']);
    Route::delete('account-types/{id}', [AcountTypeController::class, 'destroy']);
    
    // Company routes
    Route::apiResource('companies', CompanyController::class)->names('companies');
});