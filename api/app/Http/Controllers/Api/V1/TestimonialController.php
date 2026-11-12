<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTestimonialRequest;
use App\Http\Requests\Api\V1\UpdateTestimonialRequest;
use App\Http\Resources\Api\V1\TestimonialResource;
use App\Models\Testimonial;
use App\Services\MediaService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TestimonialController extends Controller
{
    /**
     * List visible Testimonials (public)
     *
     * Visible Testimonials ordered by `sort_order`. No authentication.
     */
    public function publicIndex(): AnonymousResourceCollection
    {
        return TestimonialResource::collection(Testimonial::query()->where('is_visible', true)->orderBy('sort_order')->orderBy('id')->get());
    }

    /**
     * List Testimonials
     *
     * All Testimonials ordered by `sort_order`, hidden ones included.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Testimonial::class);

        return TestimonialResource::collection(Testimonial::query()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** Show a testimonial */
    public function show(Testimonial $testimonial): TestimonialResource
    {
        Gate::authorize('view', $testimonial);

        return new TestimonialResource($testimonial);
    }

    /** Create a testimonial */
    public function store(StoreTestimonialRequest $request): TestimonialResource
    {
        // refresh(): load the DB defaults (sort_order, is_visible) omitted from the request.
        return new TestimonialResource(Testimonial::query()->create($request->validated())->refresh());
    }

    /**
     * Update a testimonial
     *
     * Partial update of the text (both languages), sort order and visibility. The image has its own endpoint.
     */
    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): TestimonialResource
    {
        $testimonial->update($request->validated());

        return new TestimonialResource($testimonial);
    }

    /** Delete a testimonial */
    public function destroy(Testimonial $testimonial, MediaService $media): Response
    {
        Gate::authorize('delete', $testimonial);

        $media->delete($testimonial->image);
        $testimonial->delete();

        return response()->noContent();
    }
}
