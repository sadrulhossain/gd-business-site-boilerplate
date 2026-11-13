<?php

namespace App\Http\Requests\Api\V1;

use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    use ResolvesPostStatus;

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('post')) ?? false;
    }

    /**
     * `image` is not editable here (it has its own endpoint).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...Bilingual::rules([
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'excerpt' => ['sometimes', 'nullable', 'string', 'max:1000'],
                'seo_title' => ['sometimes', 'nullable', 'string', 'max:70'],
                'seo_description' => ['sometimes', 'nullable', 'string', 'max:320'],
                'body' => ['sometimes', 'nullable', 'string', 'max:200000'],
            ]),
            'slug' => [
                'sometimes', 'required', 'string', 'max:150', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('posts', 'slug')->ignore($this->route('post')),
            ],
            'category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'id')],
            'is_published' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:draft,published'],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
