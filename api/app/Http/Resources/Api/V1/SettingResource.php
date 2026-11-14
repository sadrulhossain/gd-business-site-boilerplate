<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Setting;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Setting */
class SettingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'site_name' => $this->site_name,
            'site_name_2nd' => $this->site_name_2nd,
            'tagline' => $this->tagline,
            'tagline_2nd' => $this->tagline_2nd,
            'currency' => $this->currency,
            'currency_symbol' => $this->currency_symbol,
            'logo' => app(MediaService::class)->describe($this->logo),
            'favicon' => app(MediaService::class)->describe($this->favicon),
            'address' => $this->address,
            'address_2nd' => $this->address_2nd,
            'phone' => $this->phone,
            'email' => $this->email,
            'hours' => $this->hours,
            'social_links' => (object) ($this->social_links ?? []),
            'updated_at' => $this->updated_at,
        ];
    }
}
