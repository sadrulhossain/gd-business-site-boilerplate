<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Counted models. The key is the response key and the permission prefix (`<key>.view`). */
    private const COUNTED = [
        'posts' => Post::class,
        'categories' => Category::class,
        'services' => Service::class,
        'testimonials' => Testimonial::class,
        'team' => TeamMember::class,
        'gallery' => GalleryItem::class,
        'faqs' => Faq::class,
    ];

    /**
     * Dashboard counts
     *
     * Row counts for every module the user may view, plus unread contact messages.
     * Modules the user cannot view are left out.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $counts = [];

        foreach (self::COUNTED as $key => $model) {
            if ($user->can($key.'.view')) {
                $counts[$key] = $model::query()->count();
            }
        }

        if ($user->can('contact.view')) {
            $counts['unread_messages'] = ContactMessage::query()->whereNull('read_at')->count();
        }

        return response()->json(['data' => $counts]);
    }
}
