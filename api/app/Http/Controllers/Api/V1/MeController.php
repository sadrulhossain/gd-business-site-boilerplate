<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class MeController extends Controller
{
    /**
     * Current user
     *
     * The authenticated user with role and permission names. The superadmin
     * gets every permission name.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $permissions = $user->isSuperadmin()
            ? Permission::query()->where('guard_name', 'api')->orderBy('name')->pluck('name')
            : $user->getAllPermissions()->pluck('name')->sort()->values();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_superadmin' => $user->isSuperadmin(),
                'roles' => $user->getRoleNames()->sort()->values(),
                'permissions' => $permissions,
            ],
        ]);
    }
}
