<?php

namespace App\Models;

use App\Models\Concerns\HasSecondLanguage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One price option (plan) of a service. `features` holds one feature per line. */
#[Fillable([
    'service_id', 'name', 'name_2nd', 'price', 'unit', 'unit_2nd', 'description', 'description_2nd',
    'features', 'features_2nd', 'is_featured', 'sort_order', 'is_visible',
])]
class Pricing extends Model
{
    use HasSecondLanguage;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'sort_order' => 'integer', 'is_visible' => 'boolean', 'is_featured' => 'boolean'];
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
