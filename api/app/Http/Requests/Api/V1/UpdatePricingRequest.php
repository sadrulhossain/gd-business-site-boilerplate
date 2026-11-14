<?php

namespace App\Http\Requests\Api\V1;

use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('pricing')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'service_id' => ['sometimes', 'integer', 'exists:services,id'],
            ...Bilingual::rules([
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'unit' => ['sometimes', 'nullable', 'string', 'max:80'],
                'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
                'features' => ['sometimes', 'nullable', 'string', 'max:4000'],
            ]),
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
