<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('testimonials.view');
    }

    public function view(User $user, Testimonial $testimonial): bool
    {
        return $user->checkPermissionTo('testimonials.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('testimonials.create');
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $user->checkPermissionTo('testimonials.update');
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $user->checkPermissionTo('testimonials.delete');
    }
}
