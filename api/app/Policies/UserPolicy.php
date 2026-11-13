<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('users.view');
    }

    public function view(User $user, User $target): bool
    {
        return $user->checkPermissionTo('users.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('users.create');
    }

    /** Only a superadmin may change a superadmin account. */
    public function update(User $user, User $target): bool
    {
        return $user->checkPermissionTo('users.update') && ($user->isSuperadmin() || ! $target->isSuperadmin());
    }

    /** Nobody deletes their own account; only a superadmin deletes a superadmin. */
    public function delete(User $user, User $target): bool
    {
        return $user->checkPermissionTo('users.delete')
            && $user->isNot($target)
            && ($user->isSuperadmin() || ! $target->isSuperadmin());
    }
}
