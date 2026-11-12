<?php

namespace App\Models;

use App\Models\Concerns\HasSecondLanguage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A image shown in the public gallery section. */
#[Fillable(['title', 'title_2nd', 'caption', 'caption_2nd', 'image', 'sort_order', 'is_visible'])]
class GalleryItem extends Model
{
    use HasSecondLanguage;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_visible' => 'boolean'];
    }
}
