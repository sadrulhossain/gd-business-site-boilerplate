<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

/** The superadmin role is managed outside the API: it is never changed or deleted here. */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->checkPermissionTo('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->checkPermissionTo('roles.update') && $role->name !== config('access.superadmin_role');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->checkPermissionTo('roles.delete') && $role->name !== config('access.superadmin_role');
    }
}
