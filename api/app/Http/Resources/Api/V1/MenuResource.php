<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Menu */
class MenuResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            // Set by MenuController::index: rows come depth-first, so the UI can indent without rebuilding the tree.
            'depth' => $this->depth ?? 0,
            'title' => $this->title,
            'icon' => $this->icon,
            'path' => $this->path,
            'permission_name' => $this->permission_name,
            'sort' => $this->sort,
        ];
    }
}
