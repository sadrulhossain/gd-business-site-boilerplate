<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * List permissions
     *
     * Every permission name (`<module>.<action>`), for the role matrix. The route is gated by `roles.view`.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => Permission::query()->where('guard_name', 'api')->orderBy('name')->pluck('name')]);
    }
}
