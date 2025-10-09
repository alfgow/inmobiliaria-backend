<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorLogger
{
    /**
     * Persist information about the given exception for later debugging.
     */
    public function log(Throwable $exception): void
    {
        try {
            $statusCode = $this->resolveStatusCode($exception);

            if ($statusCode < 500 || $statusCode >= 600) {
                return;
            }

            $request = $this->resolveRequest();

            ErrorLog::create([
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'status_code' => $statusCode,
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'payload' => $request?->all(),
                'headers' => $request?->headers->all(),
                'user_id' => $request?->user()?->getAuthIdentifier(),
            ]);
        } catch (Throwable $internal) {
            // Intentionally swallow any internal errors to avoid cascading failures.
        }
    }

    private function resolveStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    private function resolveRequest(): ?Request
    {
        if (!app()->bound('request')) {
            return null;
        }

        $request = request();

        if (!$request instanceof Request) {
            return null;
        }

        return $request;
    }
}
