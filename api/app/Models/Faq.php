<?php

namespace App\Models;

use App\Models\Concerns\HasSecondLanguage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A question and answer shown in the public FAQ section. */
#[Fillable(['question', 'question_2nd', 'answer', 'answer_2nd', 'sort_order', 'is_visible'])]
class Faq extends Model
{
    use HasSecondLanguage;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_visible' => 'boolean'];
    }
}
