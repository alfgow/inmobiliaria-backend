<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{
    public function __construct(
        private readonly ApiTokenService $tokenService,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guard = Auth::guard('web');

        if (! $guard->validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user = $guard->getProvider()->retrieveByCredentials($credentials);

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $this->tokenService->issueTokenForUser($user);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expires_in' => $this->tokenService->ttl(),
        ]);
    }
}
