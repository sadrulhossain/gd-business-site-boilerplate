<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreHeroImageRequest;
use App\Http\Requests\Api\V1\UpdateHeroRequest;
use App\Http\Resources\Api\V1\SectionResource;
use App\Models\Section;
use App\Services\MediaService;

/** The landing-page hero: the `hero` section row (title, description, banner). Guarded by the hero.view / hero.update permissions on the routes. */
class HeroController extends Controller
{
    /**
     * Show the hero
     *
     * Title, description (both languages) and banner of the landing-page hero.
     */
    public function show(): SectionResource
    {
        return new SectionResource(Section::hero());
    }

    /**
     * Update the hero
     *
     * Partial update of the title and description (both languages).
     */
    public function update(UpdateHeroRequest $request): SectionResource
    {
        $hero = Section::hero();
        $hero->update($request->validated());

        return new SectionResource($hero);
    }

    /**
     * Upload the hero banner
     *
     * Multipart field `image` (jpeg, png or webp). Replaces the current banner.
     */
    public function storeImage(StoreHeroImageRequest $request, MediaService $media): SectionResource
    {
        $hero = Section::hero();
        $previous = $hero->image;

        $hero->update(['image' => $media->store($request->file('image'), 'sections')]);
        $media->delete($previous);

        return new SectionResource($hero);
    }

    /** Remove the hero banner */
    public function destroyImage(MediaService $media): SectionResource
    {
        $hero = Section::hero();

        $media->delete($hero->image);
        $hero->update(['image' => null]);

        return new SectionResource($hero);
    }
}
