<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ContactMessageController extends Controller
{
    /**
     * List contact messages
     *
     * Newest first, 20 per page. `?unread=1` shows only unread messages.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ContactMessage::class);

        $messages = ContactMessage::query()
            ->when($request->boolean('unread'), fn ($q) => $q->whereNull('read_at'))
            ->orderByDesc('id')
            ->paginate(20);

        return ContactMessageResource::collection($messages);
    }

    /** Show a contact message */
    public function show(ContactMessage $contactMessage): ContactMessageResource
    {
        Gate::authorize('view', $contactMessage);

        return new ContactMessageResource($contactMessage);
    }

    /**
     * Mark a contact message as read
     *
     * Idempotent: an already read message keeps its original `read_at`.
     */
    public function markRead(ContactMessage $contactMessage): ContactMessageResource
    {
        Gate::authorize('update', $contactMessage);

        if ($contactMessage->read_at === null) {
            $contactMessage->update(['read_at' => now()]);
        }

        return new ContactMessageResource($contactMessage);
    }
}
