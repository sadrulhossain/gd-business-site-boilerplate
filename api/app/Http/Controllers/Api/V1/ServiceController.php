<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreServiceRequest;
use App\Http\Requests\Api\V1\UpdateServiceRequest;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Models\Service;
use App\Services\MediaService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
{
    /**
     * List visible Services (public)
     *
     * Visible Services ordered by `sort_order`. No authentication.
     */
    public function publicIndex(): AnonymousResourceCollection
    {
        return ServiceResource::collection(Service::query()->where('is_visible', true)->orderBy('sort_order')->orderBy('id')->get());
    }

    /**
     * List Services
     *
     * All Services ordered by `sort_order`, hidden ones included.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Service::class);

        return ServiceResource::collection(Service::query()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** Show a service */
    public function show(Service $service): ServiceResource
    {
        Gate::authorize('view', $service);

        return new ServiceResource($service);
    }

    /** Create a service */
    public function store(StoreServiceRequest $request): ServiceResource
    {
        // refresh(): load the DB defaults (sort_order, is_visible) omitted from the request.
        return new ServiceResource(Service::query()->create($request->validated())->refresh());
    }

    /**
     * Update a service
     *
     * Partial update of the text (both languages), sort order and visibility. The image has its own endpoint.
     */
    public function update(UpdateServiceRequest $request, Service $service): ServiceResource
    {
        $service->update($request->validated());

        return new ServiceResource($service);
    }

    /** Delete a service */
    public function destroy(Service $service, MediaService $media): Response
    {
        Gate::authorize('delete', $service);

        $media->delete($service->image);
        $service->delete();

        return response()->noContent();
    }
}
