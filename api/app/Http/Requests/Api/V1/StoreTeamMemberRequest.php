<?php

namespace App\Http\Requests\Api\V1;

use App\Models\TeamMember;
use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TeamMember::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...Bilingual::rules([
                'role' => ['required', 'string', 'max:100'],
                'bio' => ['nullable', 'string', 'max:2000'],
            ]),
            'name' => ['required', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
