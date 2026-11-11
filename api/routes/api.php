<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\LocalesController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MeMenusController;
use App\Http\Controllers\Api\V1\SectionController;
use App\Http\Controllers\Api\V1\SectionImageController;
use App\Http\Controllers\Api\V1\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('health', HealthController::class);
    Route::get('meta/locales', LocalesController::class);

    // Authenticated routes. Module routes add ->middleware('can:<permission>').
    Route::middleware('auth:api')->group(function (): void {
        Route::get('me', MeController::class);
        Route::get('me/menus', MeMenusController::class);

        // Content (edit-only: seeded rows, no create/delete)
        Route::apiSingleton('settings', SettingController::class);
        Route::apiResource('sections', SectionController::class)->only(['index', 'show', 'update']);
        Route::post('sections/{section}/image', [SectionImageController::class, 'store']);
        Route::delete('sections/{section}/image', [SectionImageController::class, 'destroy']);
    });
});
