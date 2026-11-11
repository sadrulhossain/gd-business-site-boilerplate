<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('sections.view');
    }

    public function view(User $user, Section $section): bool
    {
        return $user->checkPermissionTo('sections.view');
    }

    public function update(User $user, Section $section): bool
    {
        return $user->checkPermissionTo('sections.update');
    }
}
