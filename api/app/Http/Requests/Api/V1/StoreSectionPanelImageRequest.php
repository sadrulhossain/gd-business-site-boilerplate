<?php

namespace App\Http\Requests\Api\V1;

use App\Services\MediaService;
use Illuminate\Foundation\Http\FormRequest;

class StoreSectionPanelImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->route('key').'.update') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['image' => ['required', ...MediaService::imageRules()]];
    }
}
