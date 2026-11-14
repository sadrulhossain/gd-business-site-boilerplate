<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Setting;
use App\Services\MediaService;
use Illuminate\Foundation\Http\FormRequest;

/** Logo and favicon uploads: multipart field `image`. */
class StoreSettingImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', Setting::current()) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['image' => ['required', ...MediaService::imageRules()]];
    }
}
