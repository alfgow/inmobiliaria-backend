<?php

namespace App\Http\Middleware;

use App\Exceptions\InvalidTokenException;
use App\Models\ApiKey;
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

        return $this->unauthorizedResponse('No se proporcionaron credenciales válidas.');
    }

    protected function authenticateWithBearerToken(string $token, Request $request, Closure $next): Response
    {
        if ($token === null) {
            return $this->unauthorizedResponse('El token Bearer no fue proporcionado.');
        }

        try {
            $payload = $this->tokenService->decode($token);
        } catch (InvalidTokenException $exception) {
            report($exception);

            return $this->unauthorizedResponse('El token Bearer es inválido.');
        }

        $userId = $payload['sub'] ?? null;

        if ($userId === null) {
            return $this->unauthorizedResponse('El token Bearer no contiene el identificador de usuario.');
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            return $this->unauthorizedResponse('El usuario asociado al token Bearer no existe.');
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
            return $this->unauthorizedResponse('La API key proporcionada no es válida.');
        }

        if ($apiKey->allowed_ip !== null && $request->ip() !== $apiKey->allowed_ip) {
            return $this->unauthorizedResponse('La dirección IP de la solicitud no está autorizada para esta API key.');
        }

        $user = $apiKey->user;

        if ($user === null) {
            return $this->unauthorizedResponse('La API key no tiene un usuario asociado.');
        }

        $apiKey->markAsUsed();

        Auth::setUser($user);
        $request->setUserResolver(static fn() => $user);

        return $next($request);
    }

    protected function unauthorizedResponse(?string $reason = null): JsonResponse
    {
        $payload = [
            'message' => 'No se pudo autenticar la solicitud API.',
        ];

        if ($reason !== null) {
            $payload['reason'] = $reason;
        }

        return response()->json($payload, 401, ['WWW-Authenticate' => 'Bearer']);
    }
}
