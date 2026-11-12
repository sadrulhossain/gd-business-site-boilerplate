<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreServiceImageRequest;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Models\Service;
use App\Services\MediaService;
use Illuminate\Support\Facades\Gate;

class ServiceImageController extends Controller
{
    /**
     * Upload a service image
     *
     * Multipart field `image` (jpeg, png or webp). Replaces the current image;
     * WebP variants are generated in the background.
     */
    public function store(StoreServiceImageRequest $request, Service $service, MediaService $media): ServiceResource
    {
        $previous = $service->image;

        $service->update(['image' => $media->store($request->file('image'), 'services')]);
        $media->delete($previous);

        return new ServiceResource($service);
    }

    /** Remove the service image */
    public function destroy(Service $service, MediaService $media): ServiceResource
    {
        Gate::authorize('update', $service);

        $media->delete($service->image);
        $service->update(['image' => null]);

        return new ServiceResource($service);
    }
}
