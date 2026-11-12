<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Service;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_2nd' => $this->title_2nd,
            'description' => $this->description,
            'description_2nd' => $this->description_2nd,
            'image' => app(MediaService::class)->describe($this->image),
            'sort_order' => $this->sort_order,
            'is_visible' => $this->is_visible,
            'updated_at' => $this->updated_at,
        ];
    }
}
