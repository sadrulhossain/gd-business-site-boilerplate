<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSettingImageRequest;
use App\Http\Resources\Api\V1\SettingResource;
use App\Models\Setting;
use App\Services\MediaService;
use Illuminate\Support\Facades\Gate;

class SettingImageController extends Controller
{
    /**
     * Upload the site logo
     *
     * Multipart field `image` (jpeg, png or webp). Replaces the current logo.
     */
    public function storeLogo(StoreSettingImageRequest $request, MediaService $media): SettingResource
    {
        return $this->store($request, $media, 'logo');
    }

    /** Remove the site logo */
    public function destroyLogo(MediaService $media): SettingResource
    {
        Gate::authorize('update', Setting::current());

        return $this->destroy($media, 'logo');
    }

    /**
     * Upload the favicon
     *
     * Multipart field `image` (jpeg, png or webp), ideally square. Replaces the current favicon.
     */
    public function storeFavicon(StoreSettingImageRequest $request, MediaService $media): SettingResource
    {
        return $this->store($request, $media, 'favicon');
    }

    /** Remove the favicon */
    public function destroyFavicon(MediaService $media): SettingResource
    {
        Gate::authorize('update', Setting::current());

        return $this->destroy($media, 'favicon');
    }

    private function store(StoreSettingImageRequest $request, MediaService $media, string $column): SettingResource
    {
        $setting = Setting::current();
        $previous = $setting->{$column};

        $setting->update([$column => $media->store($request->file('image'), 'site')]);
        $media->delete($previous);

        return new SettingResource($setting);
    }

    private function destroy(MediaService $media, string $column): SettingResource
    {
        $setting = Setting::current();

        $media->delete($setting->{$column});
        $setting->update([$column => null]);

        return new SettingResource($setting);
    }
}
