<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSectionImageRequest;
use App\Http\Resources\Api\V1\SectionResource;
use App\Models\Section;
use App\Services\MediaService;
use Illuminate\Support\Facades\Gate;

class SectionImageController extends Controller
{
    /**
     * Upload a section image
     *
     * Multipart field `image` (jpeg, png or webp). Replaces the current image;
     * WebP variants are generated in the background.
     */
    public function store(StoreSectionImageRequest $request, Section $section, MediaService $media): SectionResource
    {
        $previous = $section->image;

        $section->update(['image' => $media->store($request->file('image'), 'sections')]);
        $media->delete($previous);

        return new SectionResource($section);
    }

    /** Remove the section image */
    public function destroy(Section $section, MediaService $media): SectionResource
    {
        Gate::authorize('update', $section);

        $media->delete($section->image);
        $section->update(['image' => null]);

        return new SectionResource($section);
    }
}
