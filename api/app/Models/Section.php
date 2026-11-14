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

    /** Titles for the single-section screens, used when the row has to be created. */
    private const PANEL_TITLES = ['hero' => 'Welcome', 'about' => 'About us'];

    /** A section with a screen of its own (hero, about), created on first use. */
    public static function panel(string $key): self
    {
        return static::query()->firstOrCreate(['key' => $key], [
            'title' => self::PANEL_TITLES[$key] ?? ucfirst($key),
            'sort_order' => $key === 'hero' ? 10 : 20,
            'is_visible' => true,
        ]);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_visible' => 'boolean'];
    }
}
