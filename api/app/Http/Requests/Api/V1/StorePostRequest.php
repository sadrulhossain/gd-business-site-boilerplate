<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Post;
use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Post::class) ?? false;
    }

    /**
     * `body` is rich-text HTML; the model sanitises it on write.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...Bilingual::rules([
                'title' => ['required', 'string', 'max:255'],
                'excerpt' => ['nullable', 'string', 'max:1000'],
                'body' => ['nullable', 'string', 'max:200000'],
            ]),
            'slug' => ['required', 'string', 'max:150', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('posts', 'slug')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
