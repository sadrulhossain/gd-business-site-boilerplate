<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class LocalesController extends Controller
{
    /**
     * Supported locales
     *
     * Values come from the LOCALE_DEFAULT, LOCALE_FALLBACK and LOCALE_SUPPORTED env vars.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'default' => config('locales.default'),
                'fallback' => config('locales.fallback'),
                'supported' => config('locales.supported'),
            ],
        ]);
    }
}
