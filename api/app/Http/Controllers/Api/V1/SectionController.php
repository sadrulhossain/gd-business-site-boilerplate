<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateSectionRequest;
use App\Http\Resources\Api\V1\SectionResource;
use App\Models\Section;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SectionController extends Controller
{
    /**
     * List sections
     *
     * All sections ordered by `sort_order`. Sections are seeded, so there is no create or delete.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Section::class);

        return SectionResource::collection(Section::query()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** Show a section */
    public function show(Section $section): SectionResource
    {
        Gate::authorize('view', $section);

        return new SectionResource($section);
    }

    /**
     * Update a section
     *
     * Partial update of title, content (both languages), sort order and visibility.
     */
    public function update(UpdateSectionRequest $request, Section $section): SectionResource
    {
        $section->update($request->validated());

        return new SectionResource($section);
    }
}
