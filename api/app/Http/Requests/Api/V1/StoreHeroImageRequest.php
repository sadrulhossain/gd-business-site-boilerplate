<?php

namespace App\Http\Requests\Api\V1;

use App\Services\MediaService;
use Illuminate\Foundation\Http\FormRequest;

class StoreHeroImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('hero.update') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['image' => ['required', ...MediaService::imageRules()]];
    }
}
