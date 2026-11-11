<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One navigation entry. `permission_name` null means a group that is shown
 * only when at least one child is visible.
 */
#[Fillable(['parent_id', 'permission_name', 'title', 'sort', 'icon', 'path'])]
class Menu extends Model
{
    /** @return BelongsTo<Menu, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<Menu, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort')->orderBy('id');
    }
}
