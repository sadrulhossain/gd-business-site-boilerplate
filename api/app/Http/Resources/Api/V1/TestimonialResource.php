<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Testimonial;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Testimonial */
class TestimonialResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author_name' => $this->author_name,
            'author_role' => $this->author_role,
            'author_role_2nd' => $this->author_role_2nd,
            'quote' => $this->quote,
            'quote_2nd' => $this->quote_2nd,
            'image' => app(MediaService::class)->describe($this->image),
            'sort_order' => $this->sort_order,
            'is_visible' => $this->is_visible,
            'updated_at' => $this->updated_at,
        ];
    }
}
