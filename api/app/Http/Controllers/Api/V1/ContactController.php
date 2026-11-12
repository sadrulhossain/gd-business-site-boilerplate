<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\Setting;
use App\Notifications\NewContactMessage;
use App\Services\Captcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    /**
     * Get a contact captcha (public)
     *
     * A simple sum. Send `captcha_id` and `captcha_answer` with the message; each captcha works once.
     */
    public function captcha(Captcha $captcha): JsonResponse
    {
        return response()->json(['data' => $captcha->issue()]);
    }

    /**
     * Send a contact message (public)
     *
     * Needs a valid captcha. The `website` field is a honeypot and must be left out or empty.
     * Throttled per IP. The site owner is notified by queued email.
     */
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $message = ContactMessage::query()->create($request->safe()->only(['name', 'email', 'phone', 'subject', 'message']));

        $to = Setting::current()->email ?: config('contact.notify_to');

        if (filled($to)) {
            Notification::route('mail', $to)->notify(new NewContactMessage($message));
        }

        return response()->json(['data' => ['received' => true]], 201);
    }
}
