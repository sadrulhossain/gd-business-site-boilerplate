<?php

namespace App\Http\Requests\Api\V1;

use App\Services\MediaService;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('service')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['image' => ['required', ...MediaService::imageRules()]];
    }
}
