<?php

namespace App\Models;

use App\Models\Concerns\HasSecondLanguage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A content block of the public site, identified by an immutable `key` (the portal renders by key). */
#[Fillable(['key', 'title', 'title_2nd', 'content', 'content_2nd', 'image', 'sort_order', 'is_visible'])]
class Section extends Model
{
    use HasSecondLanguage;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_visible' => 'boolean'];
    }
}
