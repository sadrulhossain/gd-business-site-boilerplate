<?php

use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\GalleryItemController;
use App\Http\Controllers\Api\V1\GalleryItemImageController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\LocalesController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MeMenusController;
use App\Http\Controllers\Api\V1\SectionController;
use App\Http\Controllers\Api\V1\SectionImageController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\ServiceImageController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\TeamMemberController;
use App\Http\Controllers\Api\V1\TeamMemberImageController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\TestimonialImageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('health', HealthController::class);
    Route::get('meta/locales', LocalesController::class);

    // Public content (visible rows only)
    Route::get('public/faqs', [FaqController::class, 'publicIndex']);
    Route::get('public/services', [ServiceController::class, 'publicIndex']);
    Route::get('public/testimonials', [TestimonialController::class, 'publicIndex']);
    Route::get('public/team', [TeamMemberController::class, 'publicIndex']);
    Route::get('public/gallery', [GalleryItemController::class, 'publicIndex']);

    // Authenticated routes. Module routes add ->middleware('can:<permission>').
    Route::middleware('auth:api')->group(function (): void {
        Route::get('me', MeController::class);
        Route::get('me/menus', MeMenusController::class);

        // Content (edit-only: seeded rows, no create/delete)
        Route::apiSingleton('settings', SettingController::class);
        Route::apiResource('sections', SectionController::class)->only(['index', 'show', 'update']);
        Route::post('sections/{section}/image', [SectionImageController::class, 'store']);
        Route::delete('sections/{section}/image', [SectionImageController::class, 'destroy']);

        // Content lists (full CRUD)
        Route::apiResource('faqs', FaqController::class);

        Route::apiResource('services', ServiceController::class);
        Route::post('services/{service}/image', [ServiceImageController::class, 'store']);
        Route::delete('services/{service}/image', [ServiceImageController::class, 'destroy']);

        Route::apiResource('testimonials', TestimonialController::class);
        Route::post('testimonials/{testimonial}/image', [TestimonialImageController::class, 'store']);
        Route::delete('testimonials/{testimonial}/image', [TestimonialImageController::class, 'destroy']);

        Route::apiResource('team', TeamMemberController::class)->parameters(['team' => 'team_member']);
        Route::post('team/{team_member}/image', [TeamMemberImageController::class, 'store']);
        Route::delete('team/{team_member}/image', [TeamMemberImageController::class, 'destroy']);

        Route::apiResource('gallery', GalleryItemController::class)->parameters(['gallery' => 'gallery_item']);
        Route::post('gallery/{gallery_item}/image', [GalleryItemImageController::class, 'store']);
        Route::delete('gallery/{gallery_item}/image', [GalleryItemImageController::class, 'destroy']);
    });
});
