<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\OAuthClient;
use App\Models\Post;
use App\Models\Section;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Observers\ContentObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::useClientModel(OAuthClient::class);
        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addDays(30));

        // Public content changes -> queued signed webhook so the portal revalidates its cache.
        foreach ([
            Category::class, Faq::class, GalleryItem::class, Post::class, Section::class,
            Service::class, Setting::class, TeamMember::class, Testimonial::class,
        ] as $model) {
            $model::observe(ContentObserver::class);
        }

        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(config('contact.per_minute'))->by($request->ip()));

        // Superadmin passes every gate check (permissions, policies, can: middleware).
        Gate::before(fn (User $user) => $user->isSuperadmin() ? true : null);
    }
}
