<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A message sent through the public contact form. Not public content: no webhook, no bilingual columns. */
#[Fillable(['name', 'email', 'phone', 'subject', 'message', 'read_at'])]
class ContactMessage extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}
