<?php

namespace App\Policies;

use App\Models\Pricing;
use App\Models\User;

/** Superadmin is handled by Gate::before. */
class PricingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('pricing.view');
    }

    public function view(User $user, Pricing $pricing): bool
    {
        return $user->checkPermissionTo('pricing.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('pricing.create');
    }

    public function update(User $user, Pricing $pricing): bool
    {
        return $user->checkPermissionTo('pricing.update');
    }

    public function delete(User $user, Pricing $pricing): bool
    {
        return $user->checkPermissionTo('pricing.delete');
    }
}
