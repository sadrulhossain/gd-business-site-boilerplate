<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->checkPermissionTo('categories.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->checkPermissionTo('categories.update');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->checkPermissionTo('categories.delete');
    }
}
