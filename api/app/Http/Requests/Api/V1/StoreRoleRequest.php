<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Role::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_-]+$/', Rule::unique('roles', 'name')->where('guard_name', 'api'), Rule::notIn([config('access.superadmin_role')])],
            ...GrantablePermissions::rules($this->user()),
        ];
    }
}
