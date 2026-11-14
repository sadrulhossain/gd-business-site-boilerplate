<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSectionPanelImageRequest;
use App\Http\Requests\Api\V1\UpdateSectionPanelRequest;
use App\Http\Resources\Api\V1\SectionResource;
use App\Models\Section;
use App\Services\MediaService;
use Illuminate\Http\Request;

/**
 * A single section with a screen of its own (hero, about): title, text and image. The section key comes from the route
 * default (`key`); who may call each route is the `<key>.view` / `<key>.update` permission set by the route middleware.
 */
class SectionPanelController extends Controller
{
    /**
     * Show a section panel
     *
     * Title and text (both languages) and image of the hero or about section.
     */
    public function show(Request $request): SectionResource
    {
        return new SectionResource(Section::panel($this->key($request)));
    }

    /**
     * Update a section panel
     *
     * Partial update of the title and text (both languages).
     */
    public function update(UpdateSectionPanelRequest $request): SectionResource
    {
        $section = Section::panel($this->key($request));
        $section->update($request->validated());

        return new SectionResource($section);
    }

    /**
     * Upload the section image
     *
     * Multipart field `image` (jpeg, png or webp). Replaces the current image.
     */
    public function storeImage(StoreSectionPanelImageRequest $request, MediaService $media): SectionResource
    {
        $section = Section::panel($this->key($request));
        $previous = $section->image;

        $section->update(['image' => $media->store($request->file('image'), 'sections')]);
        $media->delete($previous);

        return new SectionResource($section);
    }

    /** Remove the section image */
    public function destroyImage(Request $request, MediaService $media): SectionResource
    {
        $section = Section::panel($this->key($request));

        $media->delete($section->image);
        $section->update(['image' => null]);

        return new SectionResource($section);
    }

    private function key(Request $request): string
    {
        return (string) $request->route('key');
    }
}
