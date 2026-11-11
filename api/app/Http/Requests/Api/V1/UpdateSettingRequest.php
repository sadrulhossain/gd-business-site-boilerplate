<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Setting;
use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    /** Authorization lives in the Policy; it runs before validation, so a 403 never leaks validation details. */
    public function authorize(): bool
    {
        return $this->user()?->can('update', Setting::current()) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...Bilingual::rules([
                'site_name' => ['sometimes', 'required', 'string', 'max:120'],
                'tagline' => ['sometimes', 'nullable', 'string', 'max:255'],
                'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            ]),
            'phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'hours' => ['sometimes', 'nullable', 'array:'.implode(',', Setting::DAYS)],
            'hours.*' => ['array:open,close,closed'],
            'hours.*.closed' => ['required', 'boolean'],
            'hours.*.open' => ['nullable', 'date_format:H:i'],
            'hours.*.close' => ['nullable', 'date_format:H:i'],
            'social_links' => ['sometimes', 'nullable', 'array:'.implode(',', Setting::SOCIAL)],
            'social_links.*' => ['nullable', 'url', 'max:255'],
        ];
    }
}
