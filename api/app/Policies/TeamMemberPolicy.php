<?php

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;

class TeamMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('team.view');
    }

    public function view(User $user, TeamMember $teamMember): bool
    {
        return $user->checkPermissionTo('team.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('team.create');
    }

    public function update(User $user, TeamMember $teamMember): bool
    {
        return $user->checkPermissionTo('team.update');
    }

    public function delete(User $user, TeamMember $teamMember): bool
    {
        return $user->checkPermissionTo('team.delete');
    }
}
