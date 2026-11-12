<?php

namespace App\Models;

use App\Models\Concerns\HasSecondLanguage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A customer testimonial shown in the public testimonials section. */
#[Fillable(['author_name', 'author_role', 'author_role_2nd', 'quote', 'quote_2nd', 'image', 'sort_order', 'is_visible'])]
class Testimonial extends Model
{
    use HasSecondLanguage;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_visible' => 'boolean'];
    }
}
