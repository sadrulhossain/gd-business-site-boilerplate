<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateSettingRequest;
use App\Http\Resources\Api\V1\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    /**
     * Show settings (public)
     *
     * The single settings record, for the site header, footer and metadata. No authentication.
     */
    public function publicShow(): JsonResponse
    {
        // 200 even when the default row was just created (a resource answers 201 for a fresh model).
        return (new SettingResource(Setting::current()))->response()->setStatusCode(200);
    }

    /**
     * Show settings
     *
     * The single settings record (edit-only: there is no create or delete).
     */
    public function show(): SettingResource
    {
        $setting = Setting::current();
        Gate::authorize('view', $setting);

        return new SettingResource($setting);
    }

    /**
     * Update settings
     *
     * Partial update; only the fields sent are changed.
     */
    public function update(UpdateSettingRequest $request): SettingResource
    {
        $setting = Setting::current();
        $setting->update($request->validated());

        return new SettingResource($setting);
    }
}
