<?php

namespace App\Http\Requests\Api\V1;

use App\Models\GalleryItem;
use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GalleryItem::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...Bilingual::rules([
                'title' => ['nullable', 'string', 'max:150'],
                'caption' => ['nullable', 'string', 'max:255'],
            ]),
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
