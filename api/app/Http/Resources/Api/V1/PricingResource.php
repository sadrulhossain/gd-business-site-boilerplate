<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Pricing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pricing */
class PricingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'service' => $this->whenLoaded('service', fn () => [
                'id' => $this->service->id,
                'title' => $this->service->title,
                'title_2nd' => $this->service->title_2nd,
            ]),
            'name' => $this->name,
            'name_2nd' => $this->name_2nd,
            'price' => $this->price,
            'unit' => $this->unit,
            'unit_2nd' => $this->unit_2nd,
            'description' => $this->description,
            'description_2nd' => $this->description_2nd,
            'features' => $this->features,
            'features_2nd' => $this->features_2nd,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
            'is_visible' => $this->is_visible,
            'updated_at' => $this->updated_at,
        ];
    }
}
