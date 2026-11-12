<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Testimonial;
use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Testimonial::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...Bilingual::rules([
                'author_role' => ['nullable', 'string', 'max:100'],
                'quote' => ['required', 'string', 'max:2000'],
            ]),
            'author_name' => ['required', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
