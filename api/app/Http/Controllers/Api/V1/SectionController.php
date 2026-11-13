<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReorderSectionsRequest;
use App\Http\Requests\Api\V1\UpdateSectionRequest;
use App\Http\Resources\Api\V1\SectionResource;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
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

    /**
     * Reorder sections
     *
     * `ids` is the new order; `sort_order` becomes 10, 20, 30... in that sequence.
     */
    public function reorder(ReorderSectionsRequest $request): JsonResponse
    {
        DB::transaction(function () use ($request): void {
            foreach ($request->ids() as $i => $id) {
                Section::query()->whereKey($id)->update(['sort_order' => ($i + 1) * 10]);
            }
        });

        return response()->json(['data' => ['reordered' => count($request->ids())]]);
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
