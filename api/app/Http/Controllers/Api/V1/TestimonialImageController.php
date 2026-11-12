<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTestimonialImageRequest;
use App\Http\Resources\Api\V1\TestimonialResource;
use App\Models\Testimonial;
use App\Services\MediaService;
use Illuminate\Support\Facades\Gate;

class TestimonialImageController extends Controller
{
    /**
     * Upload a testimonial image
     *
     * Multipart field `image` (jpeg, png or webp). Replaces the current image;
     * WebP variants are generated in the background.
     */
    public function store(StoreTestimonialImageRequest $request, Testimonial $testimonial, MediaService $media): TestimonialResource
    {
        $previous = $testimonial->image;

        $testimonial->update(['image' => $media->store($request->file('image'), 'testimonials')]);
        $media->delete($previous);

        return new TestimonialResource($testimonial);
    }

    /** Remove the testimonial image */
    public function destroy(Testimonial $testimonial, MediaService $media): TestimonialResource
    {
        Gate::authorize('update', $testimonial);

        $media->delete($testimonial->image);
        $testimonial->update(['image' => null]);

        return new TestimonialResource($testimonial);
    }
}
