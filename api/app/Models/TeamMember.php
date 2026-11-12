<?php

namespace App\Models;

use App\Models\Concerns\HasSecondLanguage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A team member shown in the public team section. */
#[Fillable(['name', 'role', 'role_2nd', 'bio', 'bio_2nd', 'image', 'sort_order', 'is_visible'])]
class TeamMember extends Model
{
    use HasSecondLanguage;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_visible' => 'boolean'];
    }
}
