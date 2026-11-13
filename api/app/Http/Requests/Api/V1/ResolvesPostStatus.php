<?php

namespace App\Http\Requests\Api\V1;

/** `status` (draft|published) is the admin's view of `is_published`; when sent it wins. */
trait ResolvesPostStatus
{
    protected function prepareForValidation(): void
    {
        if (in_array($this->input('status'), ['draft', 'published'], true)) {
            $this->merge(['is_published' => $this->input('status') === 'published']);
        }
    }
}
