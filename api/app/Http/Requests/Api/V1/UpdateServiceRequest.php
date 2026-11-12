<?php

namespace App\Http\Requests\Api\V1;

use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('service')) ?? false;
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
                'title' => ['sometimes', 'required', 'string', 'max:150'],
                'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            ]),
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
