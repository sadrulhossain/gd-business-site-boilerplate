<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public reads accept `?lang=<locale>`. With it, every `x` / `x_2nd` pair collapses into a single `x` in that
 * language (the second language falls back to the first when its twin is empty) and the `*_2nd` keys are dropped.
 * Without it, responses keep both languages.
 */
class LocalizePublicResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->query('lang');

        if ($lang === null) {
            return $next($request);
        }

        if (! is_string($lang) || ! in_array($lang, config('locales.supported'), true)) {
            throw ValidationException::withMessages(['lang' => 'Unsupported language.']);
        }

        App::setLocale($lang);

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->setData($this->collapse($response->getData(true), $lang === config('locales.second')));
        }

        return $response;
    }

    private function collapse(mixed $value, bool $useSecond): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            if (is_string($key) && str_ends_with($key, '_2nd')) {
                $base = substr($key, 0, -4);

                if ($useSecond && filled($item) && array_key_exists($base, $value)) {
                    $value[$base] = $item;
                }

                unset($value[$key]);

                continue;
            }

            $value[$key] = $this->collapse($item, $useSecond);
        }

        return $value;
    }
}
