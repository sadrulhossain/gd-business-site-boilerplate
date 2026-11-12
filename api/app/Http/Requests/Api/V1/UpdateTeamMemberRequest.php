<?php

namespace App\Http\Requests\Api\V1;

use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('team_member')) ?? false;
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
                'role' => ['sometimes', 'required', 'string', 'max:100'],
                'bio' => ['sometimes', 'nullable', 'string', 'max:2000'],
            ]),
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
