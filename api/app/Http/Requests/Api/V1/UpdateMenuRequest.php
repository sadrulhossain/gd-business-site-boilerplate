<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('menu')) ?? false;
    }

    /**
     * Only the label and icon: path and permission belong to the module that seeded the row.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:120'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:60'],
        ];
    }
}
