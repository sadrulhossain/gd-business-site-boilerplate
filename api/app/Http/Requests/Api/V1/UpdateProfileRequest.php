<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Changing the password needs the current one. Email is not editable here.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'current_password' => ['required_with:password', 'string', 'current_password:api'],
            'password' => ['sometimes', 'string', 'min:12', 'max:255', 'confirmed', 'different:current_password'],
        ];
    }
}
