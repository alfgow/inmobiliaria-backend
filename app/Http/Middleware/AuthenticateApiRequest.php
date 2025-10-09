<?php

namespace App\Http\Middleware;

use App\Exceptions\InvalidTokenException;
use App\Models\User;
use App\Services\ApiTokenService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiRequest
{
    public function __construct(
        private readonly ApiTokenService $tokenService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token !== null) {
            return $this->authenticateWithBearerToken($token, $request, $next);
        }

        $apiKeyValue = (string) $request->headers->get('X-Api-Key', '');

        if ($apiKeyValue !== '') {
            return $this->authenticateWithApiKey($apiKeyValue, $request, $next);
        }

        return $this->unauthorizedResponse();
    }

    protected function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'No se pudo autenticar la solicitud API.',
        ], 401, ['WWW-Authenticate' => 'Bearer']);
    }

    protected function authenticateWithBearerToken(string $token, Request $request, Closure $next): Response
    {
        if ($token === null) {
            return $this->unauthorizedResponse();
        }

        try {
            $payload = $this->tokenService->decode($token);
        } catch (InvalidTokenException $exception) {
            report($exception);

            return $this->unauthorizedResponse();
        }

        $userId = $payload['sub'] ?? null;

        if ($userId === null) {
            return $this->unauthorizedResponse();
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            return $this->unauthorizedResponse();
        }

        Auth::setUser($user);
        $request->setUserResolver(static fn() => $user);

        return $next($request);
    }

    protected function authenticateWithApiKey(string $providedKey, Request $request, Closure $next): Response
    {
        $hash = hash('sha256', $providedKey);

        $apiKey = ApiKey::query()->where('key_hash', $hash)->first();

        if ($apiKey === null) {
            return $this->unauthorizedResponse();
        }

        $user = $apiKey->user;

        if ($user === null) {
            return $this->unauthorizedResponse();
        }

        $apiKey->markAsUsed();

        Auth::setUser($user);
        $request->setUserResolver(static fn() => $user);

        return $next($request);
    protected function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'No se pudo autenticar la solicitud API.',
        ], 401, ['WWW-Authenticate' => 'Bearer']);
    }
}
