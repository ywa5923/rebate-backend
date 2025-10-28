<?php

use Illuminate\Support\Facades\Route;
use Modules\Translations\Http\Controllers\LocaleController;
use Modules\Translations\Http\Controllers\TranslationController;
use Modules\Translations\Http\Controllers\LocaleResourceController;
use Modules\Translations\Http\Controllers\ZoneController;

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
//    // Route::apiResource('translations', TranslationController::class)->names('translations');
// });

Route::prefix('v1')->group(function () {
    Route::apiResource('translations', TranslationController::class)->names('translations');
    Route::get("locale_resources",[LocaleResourceController::class,'index']);
   // Route::get("zones",[ZoneController::class,'index']);
    
    Route::apiResource("locales",LocaleController::class)->names('locales');
});
