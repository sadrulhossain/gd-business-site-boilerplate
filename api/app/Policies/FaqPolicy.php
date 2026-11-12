<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('faqs.view');
    }

    public function view(User $user, Faq $faq): bool
    {
        return $user->checkPermissionTo('faqs.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('faqs.create');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->checkPermissionTo('faqs.update');
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $user->checkPermissionTo('faqs.delete');
    }
}
