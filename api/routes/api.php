<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ContactMessageController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\GalleryItemController;
use App\Http\Controllers\Api\V1\GalleryItemImageController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\HeroController;
use App\Http\Controllers\Api\V1\LocalesController;
use App\Http\Controllers\Api\V1\LogoutController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MeMenusController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\PostImageController;
use App\Http\Controllers\Api\V1\PricingController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SectionController;
use App\Http\Controllers\Api\V1\SectionImageController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\ServiceImageController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\SettingImageController;
use App\Http\Controllers\Api\V1\TeamMemberController;
use App\Http\Controllers\Api\V1\TeamMemberImageController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\TestimonialImageController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Middleware\LocalizePublicResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('health', HealthController::class);
    Route::get('meta/locales', LocalesController::class);

    // Public content (visible rows only). `?lang=` collapses bilingual pairs into one language.
    Route::middleware(LocalizePublicResponse::class)->group(function (): void {
        Route::get('public/settings', [SettingController::class, 'publicShow']);
        Route::get('public/sections', [SectionController::class, 'publicIndex']);
        Route::get('public/faqs', [FaqController::class, 'publicIndex']);
        Route::get('public/pricing', [PricingController::class, 'publicIndex']);
        Route::get('public/services', [ServiceController::class, 'publicIndex']);
        Route::get('public/services/{service}', [ServiceController::class, 'publicShow']);
        Route::get('public/testimonials', [TestimonialController::class, 'publicIndex']);
        Route::get('public/team', [TeamMemberController::class, 'publicIndex']);
        Route::get('public/gallery', [GalleryItemController::class, 'publicIndex']);
        Route::get('public/categories', [CategoryController::class, 'publicIndex']);
        Route::get('public/posts', [PostController::class, 'publicIndex']);
        Route::get('public/posts/{slug}', [PostController::class, 'publicShow']);
    });

    // Contact form: captcha first, then the throttled submit.
    Route::get('public/captcha', [ContactController::class, 'captcha'])->middleware('throttle:30,1');
    Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:contact');

    // Authenticated routes. Module routes add ->middleware('can:<permission>').
    Route::middleware('auth:api')->group(function (): void {
        Route::get('me', MeController::class);
        Route::get('me/menus', MeMenusController::class);
        Route::patch('me', [ProfileController::class, 'update']);
        Route::post('logout', LogoutController::class);
        Route::get('dashboard', DashboardController::class)->middleware('can:dashboard.view');

        // Content (edit-only: seeded rows, no create/delete)
        Route::get('hero', [HeroController::class, 'show'])->middleware('can:hero.view');
        Route::patch('hero', [HeroController::class, 'update'])->middleware('can:hero.update');
        Route::post('hero/image', [HeroController::class, 'storeImage'])->middleware('can:hero.update');
        Route::delete('hero/image', [HeroController::class, 'destroyImage'])->middleware('can:hero.update');
        Route::post('settings/logo', [SettingImageController::class, 'storeLogo']);
        Route::delete('settings/logo', [SettingImageController::class, 'destroyLogo']);
        Route::post('settings/favicon', [SettingImageController::class, 'storeFavicon']);
        Route::delete('settings/favicon', [SettingImageController::class, 'destroyFavicon']);
        Route::apiSingleton('settings', SettingController::class);
        Route::put('sections/reorder', [SectionController::class, 'reorder']);
        Route::apiResource('sections', SectionController::class)->only(['index', 'show', 'update']);
        Route::post('sections/{section}/image', [SectionImageController::class, 'store']);
        Route::delete('sections/{section}/image', [SectionImageController::class, 'destroy']);

        // Access: users, roles (+ the permission list for the matrix), menu tree
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::get('permissions', PermissionController::class)->middleware('can:roles.view');
        Route::put('menus/reorder', [MenuController::class, 'reorder']);
        Route::get('menus', [MenuController::class, 'index']);
        Route::patch('menus/{menu}', [MenuController::class, 'update']);

        // Content lists (full CRUD)
        Route::apiResource('faqs', FaqController::class);
        Route::apiResource('pricing', PricingController::class);

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

        // Blog
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('posts', PostController::class);
        Route::post('posts/{post}/image', [PostImageController::class, 'store']);
        Route::delete('posts/{post}/image', [PostImageController::class, 'destroy']);

        // Contact inbox (no create/delete)
        Route::apiResource('contact-messages', ContactMessageController::class)
            ->only(['index', 'show'])->parameters(['contact-messages' => 'contact_message']);
        Route::post('contact-messages/{contact_message}/read', [ContactMessageController::class, 'markRead']);
    });
});
