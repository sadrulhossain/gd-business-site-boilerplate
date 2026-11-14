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

    /** The landing-page hero row, created on first use. */
    public static function hero(): self
    {
        return static::query()->firstOrCreate(['key' => 'hero'], ['title' => 'Welcome', 'sort_order' => 10, 'is_visible' => true]);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_visible' => 'boolean'];
    }
}
