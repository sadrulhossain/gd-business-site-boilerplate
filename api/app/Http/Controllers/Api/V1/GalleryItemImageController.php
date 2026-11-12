<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGalleryItemImageRequest;
use App\Http\Resources\Api\V1\GalleryItemResource;
use App\Models\GalleryItem;
use App\Services\MediaService;
use Illuminate\Support\Facades\Gate;

class GalleryItemImageController extends Controller
{
    /**
     * Upload a gallery item image
     *
     * Multipart field `image` (jpeg, png or webp). Replaces the current image;
     * WebP variants are generated in the background.
     */
    public function store(StoreGalleryItemImageRequest $request, GalleryItem $galleryItem, MediaService $media): GalleryItemResource
    {
        $previous = $galleryItem->image;

        $galleryItem->update(['image' => $media->store($request->file('image'), 'gallery')]);
        $media->delete($previous);

        return new GalleryItemResource($galleryItem);
    }

    /** Remove the gallery item image */
    public function destroy(GalleryItem $galleryItem, MediaService $media): GalleryItemResource
    {
        Gate::authorize('update', $galleryItem);

        $media->delete($galleryItem->image);
        $galleryItem->update(['image' => null]);

        return new GalleryItemResource($galleryItem);
    }
}
