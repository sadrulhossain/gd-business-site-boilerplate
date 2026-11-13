<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // The superadmin role is not assignable here, so it is left out of the editable list.
            'roles' => $this->roles->pluck('name')->reject(fn ($n) => $n === config('access.superadmin_role'))->values(),
            'is_superadmin' => $this->isSuperadmin(),
            'created_at' => $this->created_at,
        ];
    }
}
