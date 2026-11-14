<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePricingRequest;
use App\Http\Requests\Api\V1\UpdatePricingRequest;
use App\Http\Resources\Api\V1\PricingResource;
use App\Models\Pricing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PricingController extends Controller
{
    /**
     * List prices (public)
     *
     * Visible prices of visible services, ordered by service then `sort_order`. Optional `service_id` filter. No authentication.
     */
    public function publicIndex(Request $request): AnonymousResourceCollection
    {
        $prices = Pricing::query()
            ->with('service')
            ->join('services', 'services.id', '=', 'pricings.service_id')
            ->where('pricings.is_visible', true)
            ->where('services.is_visible', true)
            ->when($request->integer('service_id'), fn ($q, $id) => $q->where('pricings.service_id', $id))
            ->orderBy('services.sort_order')
            ->orderBy('services.id')
            ->orderBy('pricings.sort_order')
            ->orderBy('pricings.id')
            ->select('pricings.*')
            ->get();

        return PricingResource::collection($prices);
    }

    /**
     * List prices
     *
     * All prices with their service, hidden ones included.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Pricing::class);

        return PricingResource::collection(Pricing::query()->with('service')->orderBy('service_id')->orderBy('sort_order')->orderBy('id')->get());
    }

    /** Show a price */
    public function show(Pricing $pricing): PricingResource
    {
        Gate::authorize('view', $pricing);

        return new PricingResource($pricing->load('service'));
    }

    /** Create a price for a service */
    public function store(StorePricingRequest $request): PricingResource
    {
        return new PricingResource(Pricing::query()->create($request->validated())->refresh()->load('service'));
    }

    /**
     * Update a price
     *
     * Partial update of the service, name, price, unit, description and features (both languages), flags and order.
     */
    public function update(UpdatePricingRequest $request, Pricing $pricing): PricingResource
    {
        $pricing->update($request->validated());

        return new PricingResource($pricing->load('service'));
    }

    /** Delete a price */
    public function destroy(Pricing $pricing): Response
    {
        Gate::authorize('delete', $pricing);

        $pricing->delete();

        return response()->noContent();
    }
}
