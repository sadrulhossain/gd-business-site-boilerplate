<?php

namespace App\Models;

use App\Models\Concerns\HasSecondLanguage;
use App\Services\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A blog post. `body` holds rich-text HTML, sanitised on every write (see HtmlSanitizer). */
#[Fillable([
    'category_id', 'title', 'title_2nd', 'slug', 'excerpt', 'excerpt_2nd', 'body', 'body_2nd',
    'seo_title', 'seo_title_2nd', 'seo_description', 'seo_description_2nd',
    'image', 'is_published', 'published_at',
])]
class Post extends Model
{
    use HasSecondLanguage;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }

    /** @return Attribute<string|null, string|null> */
    protected function body(): Attribute
    {
        return Attribute::set(fn (?string $html) => app(HtmlSanitizer::class)->clean($html));
    }

    /** @return Attribute<string|null, string|null> */
    protected function body2nd(): Attribute
    {
        return Attribute::set(fn (?string $html) => app(HtmlSanitizer::class)->clean($html));
    }

    /**
     * Published and not scheduled for later.
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
