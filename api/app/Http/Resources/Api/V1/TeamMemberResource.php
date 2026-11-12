<?php

namespace App\Http\Resources\Api\V1;

use App\Models\TeamMember;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TeamMember */
class TeamMemberResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'role_2nd' => $this->role_2nd,
            'bio' => $this->bio,
            'bio_2nd' => $this->bio_2nd,
            'image' => app(MediaService::class)->describe($this->image),
            'sort_order' => $this->sort_order,
            'is_visible' => $this->is_visible,
            'updated_at' => $this->updated_at,
        ];
    }
}
