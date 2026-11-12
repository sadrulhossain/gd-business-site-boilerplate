<?php

namespace App\Policies;

use App\Models\GalleryItem;
use App\Models\User;

class GalleryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('gallery.view');
    }

    public function view(User $user, GalleryItem $galleryItem): bool
    {
        return $user->checkPermissionTo('gallery.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('gallery.create');
    }

    public function update(User $user, GalleryItem $galleryItem): bool
    {
        return $user->checkPermissionTo('gallery.update');
    }

    public function delete(User $user, GalleryItem $galleryItem): bool
    {
        return $user->checkPermissionTo('gallery.delete');
    }
}
