<?php

namespace App\Models;

use App\Models\Concerns\HasSecondLanguage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A service shown in the public services section. */
#[Fillable(['title', 'title_2nd', 'description', 'description_2nd', 'image', 'sort_order', 'is_visible'])]
class Service extends Model
{
    use HasSecondLanguage;

    /** @return HasMany<Pricing, $this> */
    public function pricings(): HasMany
    {
        return $this->hasMany(Pricing::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_visible' => 'boolean'];
    }
}
