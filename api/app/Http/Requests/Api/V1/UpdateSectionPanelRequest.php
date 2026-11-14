<?php

namespace App\Http\Requests\Api\V1;

use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionPanelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->route('key').'.update') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return Bilingual::rules([
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'content' => ['sometimes', 'nullable', 'string', 'max:10000'],
        ]);
    }
}
