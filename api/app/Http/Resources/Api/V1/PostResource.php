<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Post;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'title' => $this->title,
            'title_2nd' => $this->title_2nd,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'excerpt_2nd' => $this->excerpt_2nd,
            'body' => $this->body,
            'body_2nd' => $this->body_2nd,
            'image' => app(MediaService::class)->describe($this->image),
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
