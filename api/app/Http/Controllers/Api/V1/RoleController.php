<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRoleRequest;
use App\Http\Requests\Api\V1\UpdateRoleRequest;
use App\Http\Resources\Api\V1\RoleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * List roles
     *
     * Every role except the superadmin, with its permission names. Not paginated.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Role::class);

        return RoleResource::collection(
            Role::query()->where('guard_name', 'api')->where('name', '!=', config('access.superadmin_role'))->with('permissions')->orderBy('name')->get(),
        );
    }

    /** Show a role */
    public function show(Role $role): RoleResource
    {
        Gate::authorize('view', $role);

        return new RoleResource($role->load('permissions'));
    }

    /** Create a role */
    public function store(StoreRoleRequest $request): RoleResource
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'api']);
        $role->syncPermissions($request->validated('permissions', []));

        return new RoleResource($role->load('permissions'));
    }

    /**
     * Update a role
     *
     * `permissions` replaces the role's permission names. A permission you do not hold yourself is rejected (422).
     */
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        if ($request->has('name')) {
            $role->update(['name' => $request->validated('name')]);
        }

        if ($request->has('permissions')) {
            $role->syncPermissions($request->validated('permissions'));
        }

        return new RoleResource($role->load('permissions'));
    }

    /** Delete a role */
    public function destroy(Role $role): Response
    {
        Gate::authorize('delete', $role);
        $role->delete();

        return response()->noContent();
    }
}
