<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTeamMemberRequest;
use App\Http\Requests\Api\V1\UpdateTeamMemberRequest;
use App\Http\Resources\Api\V1\TeamMemberResource;
use App\Models\TeamMember;
use App\Services\MediaService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TeamMemberController extends Controller
{
    /**
     * List visible team members (public)
     *
     * Visible team members ordered by `sort_order`. No authentication.
     */
    public function publicIndex(): AnonymousResourceCollection
    {
        return TeamMemberResource::collection(TeamMember::query()->where('is_visible', true)->orderBy('sort_order')->orderBy('id')->get());
    }

    /**
     * List team members
     *
     * All team members ordered by `sort_order`, hidden ones included.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', TeamMember::class);

        return TeamMemberResource::collection(TeamMember::query()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** Show a team member */
    public function show(TeamMember $teamMember): TeamMemberResource
    {
        Gate::authorize('view', $teamMember);

        return new TeamMemberResource($teamMember);
    }

    /** Create a team member */
    public function store(StoreTeamMemberRequest $request): TeamMemberResource
    {
        // refresh(): load the DB defaults (sort_order, is_visible) omitted from the request.
        return new TeamMemberResource(TeamMember::query()->create($request->validated())->refresh());
    }

    /**
     * Update a team member
     *
     * Partial update of the text (both languages), sort order and visibility. The image has its own endpoint.
     */
    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): TeamMemberResource
    {
        $teamMember->update($request->validated());

        return new TeamMemberResource($teamMember);
    }

    /** Delete a team member */
    public function destroy(TeamMember $teamMember, MediaService $media): Response
    {
        Gate::authorize('delete', $teamMember);

        $media->delete($teamMember->image);
        $teamMember->delete();

        return response()->noContent();
    }
}
