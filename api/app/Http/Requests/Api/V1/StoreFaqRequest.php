<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Faq;
use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Faq::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...Bilingual::rules([
                'question' => ['required', 'string', 'max:255'],
                'answer' => ['required', 'string', 'max:10000'],
            ]),
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
