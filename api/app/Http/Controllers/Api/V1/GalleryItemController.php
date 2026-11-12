<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGalleryItemRequest;
use App\Http\Requests\Api\V1\UpdateGalleryItemRequest;
use App\Http\Resources\Api\V1\GalleryItemResource;
use App\Models\GalleryItem;
use App\Services\MediaService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class GalleryItemController extends Controller
{
    /**
     * List visible gallery items (public)
     *
     * Visible gallery items ordered by `sort_order`. No authentication.
     */
    public function publicIndex(): AnonymousResourceCollection
    {
        return GalleryItemResource::collection(GalleryItem::query()->where('is_visible', true)->orderBy('sort_order')->orderBy('id')->get());
    }

    /**
     * List gallery items
     *
     * All gallery items ordered by `sort_order`, hidden ones included.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', GalleryItem::class);

        return GalleryItemResource::collection(GalleryItem::query()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** Show a gallery item */
    public function show(GalleryItem $galleryItem): GalleryItemResource
    {
        Gate::authorize('view', $galleryItem);

        return new GalleryItemResource($galleryItem);
    }

    /** Create a gallery item */
    public function store(StoreGalleryItemRequest $request): GalleryItemResource
    {
        // refresh(): load the DB defaults (sort_order, is_visible) omitted from the request.
        return new GalleryItemResource(GalleryItem::query()->create($request->validated())->refresh());
    }

    /**
     * Update a gallery item
     *
     * Partial update of the text (both languages), sort order and visibility. The image has its own endpoint.
     */
    public function update(UpdateGalleryItemRequest $request, GalleryItem $galleryItem): GalleryItemResource
    {
        $galleryItem->update($request->validated());

        return new GalleryItemResource($galleryItem);
    }

    /** Delete a gallery item */
    public function destroy(GalleryItem $galleryItem, MediaService $media): Response
    {
        Gate::authorize('delete', $galleryItem);

        $media->delete($galleryItem->image);
        $galleryItem->delete();

        return response()->noContent();
    }
}
