<?php

namespace App\Http\Requests\Api\V1;

use App\Models\User;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

/** Rules for a `permissions` list: a non-superadmin can only hand out permissions they hold themselves. */
final class GrantablePermissions
{
    /** @return array<string, mixed> */
    public static function rules(?User $actor): array
    {
        $allowed = $actor?->isSuperadmin()
            ? Permission::query()->where('guard_name', 'api')->pluck('name')
            : ($actor?->getAllPermissions()->pluck('name') ?? collect());

        return [
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in($allowed->all())],
        ];
    }
}
