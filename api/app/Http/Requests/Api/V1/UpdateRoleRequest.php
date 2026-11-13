<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('role')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:60', 'regex:/^[a-z0-9_-]+$/', Rule::unique('roles', 'name')->where('guard_name', 'api')->ignore($this->route('role')), Rule::notIn([config('access.superadmin_role')])],
            ...GrantablePermissions::rules($this->user()),
        ];
    }
}
