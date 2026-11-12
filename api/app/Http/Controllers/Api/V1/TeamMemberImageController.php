<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTeamMemberImageRequest;
use App\Http\Resources\Api\V1\TeamMemberResource;
use App\Models\TeamMember;
use App\Services\MediaService;
use Illuminate\Support\Facades\Gate;

class TeamMemberImageController extends Controller
{
    /**
     * Upload a team member image
     *
     * Multipart field `image` (jpeg, png or webp). Replaces the current image;
     * WebP variants are generated in the background.
     */
    public function store(StoreTeamMemberImageRequest $request, TeamMember $teamMember, MediaService $media): TeamMemberResource
    {
        $previous = $teamMember->image;

        $teamMember->update(['image' => $media->store($request->file('image'), 'team')]);
        $media->delete($previous);

        return new TeamMemberResource($teamMember);
    }

    /** Remove the team member image */
    public function destroy(TeamMember $teamMember, MediaService $media): TeamMemberResource
    {
        Gate::authorize('update', $teamMember);

        $media->delete($teamMember->image);
        $teamMember->update(['image' => null]);

        return new TeamMemberResource($teamMember);
    }
}
