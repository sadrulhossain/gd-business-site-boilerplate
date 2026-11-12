<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Service;
use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Service::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...Bilingual::rules([
                'title' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:5000'],
            ]),
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
