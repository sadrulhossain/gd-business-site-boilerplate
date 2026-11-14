<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Pricing;
use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class StorePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Pricing::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            ...Bilingual::rules([
                'name' => ['required', 'string', 'max:255'],
                'unit' => ['nullable', 'string', 'max:80'],
                'description' => ['nullable', 'string', 'max:2000'],
                'features' => ['nullable', 'string', 'max:4000'],
            ]),
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
