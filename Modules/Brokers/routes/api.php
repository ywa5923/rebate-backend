<?php

use Illuminate\Support\Facades\Route;
use Modules\Brokers\Http\Controllers\BrokerController;

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
});