<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFaqRequest;
use App\Http\Requests\Api\V1\UpdateFaqRequest;
use App\Http\Resources\Api\V1\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class FaqController extends Controller
{
    /**
     * List visible FAQs (public)
     *
     * Visible FAQs ordered by `sort_order`. No authentication.
     */
    public function publicIndex(): AnonymousResourceCollection
    {
        return FaqResource::collection(Faq::query()->where('is_visible', true)->orderBy('sort_order')->orderBy('id')->get());
    }

    /**
     * List FAQs
     *
     * All FAQs ordered by `sort_order`, hidden ones included.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Faq::class);

        return FaqResource::collection(Faq::query()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** Show a FAQ */
    public function show(Faq $faq): FaqResource
    {
        Gate::authorize('view', $faq);

        return new FaqResource($faq);
    }

    /** Create a FAQ */
    public function store(StoreFaqRequest $request): FaqResource
    {
        // refresh(): load the DB defaults (sort_order, is_visible) omitted from the request.
        return new FaqResource(Faq::query()->create($request->validated())->refresh());
    }

    /**
     * Update a FAQ
     *
     * Partial update of question, answer (both languages), sort order and visibility.
     */
    public function update(UpdateFaqRequest $request, Faq $faq): FaqResource
    {
        $faq->update($request->validated());

        return new FaqResource($faq);
    }

    /** Delete a FAQ */
    public function destroy(Faq $faq): Response
    {
        Gate::authorize('delete', $faq);

        $faq->delete();

        return response()->noContent();
    }
}
