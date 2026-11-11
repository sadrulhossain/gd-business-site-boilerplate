<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Renders every exception as {"error": {"code", "message", "details"?}}.
 */
final class JsonErrorRenderer
{
    public function __invoke(Throwable $e, Request $request): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->response(422, 'validation_failed', $e->getMessage(), $e->errors());
        }

        if ($e instanceof AuthenticationException) {
            return $this->response(401, 'unauthenticated', 'Unauthenticated.');
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $status === 404
                ? 'Not found.'
                : ($e->getMessage() !== '' ? $e->getMessage() : (Response::$statusTexts[$status] ?? 'Error'));

            return $this->response($status, $this->codeFor($status), $message, headers: $e->getHeaders());
        }

        return $this->response(500, 'server_error', config('app.debug') ? $e->getMessage() : 'Server error.');
    }

    /**
     * @param  array<string, mixed>|null  $details
     * @param  array<string, string>  $headers
     */
    private function response(int $status, string $code, string $message, ?array $details = null, array $headers = []): JsonResponse
    {
        $error = ['code' => $code, 'message' => $message];

        if ($details !== null) {
            $error['details'] = $details;
        }

        return new JsonResponse(['error' => $error], $status, $headers);
    }

    private function codeFor(int $status): string
    {
        return match ($status) {
            400 => 'bad_request',
            401 => 'unauthenticated',
            403 => 'forbidden',
            404 => 'not_found',
            405 => 'method_not_allowed',
            409 => 'conflict',
            419 => 'page_expired',
            422 => 'unprocessable',
            429 => 'too_many_requests',
            default => $status >= 500 ? 'server_error' : 'http_error',
        };
    }
}
