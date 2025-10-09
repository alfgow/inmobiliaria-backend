<?php

namespace App\Services;

use App\Exceptions\InvalidTokenException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use JsonException;
use RuntimeException;

class ApiTokenService
{
    public function __construct(
        private readonly string $secret,
        private readonly int $ttl,
        private readonly ?string $issuer = null,
    ) {
        if ($this->secret === '') {
            throw new RuntimeException('API token secret is not configured.');
        }
    }

    public function issueTokenForUser(Authenticatable $user): string
    {
        $issuedAt = now();
        $expiresAt = $issuedAt->copy()->addSeconds($this->ttl);

        $payload = array_filter([
            'iss' => $this->issuer,
            'sub' => $user->getAuthIdentifier(),
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ], static fn($value) => $value !== null && $value !== '');

        return $this->encode($payload);
    }

    public function decode(string $token): array
    {
        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            throw new InvalidTokenException('El token proporcionado es inválido.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $segments;

        $header = $this->decodeJsonSegment($encodedHeader);

        if (($header['alg'] ?? null) !== 'HS256') {
            throw new InvalidTokenException('Algoritmo de firma no soportado.');
        }

        $payload = $this->decodeJsonSegment($encodedPayload);
        $signature = $this->base64UrlDecode($encodedSignature);
        $expectedSignature = hash_hmac('sha256', $encodedHeader.'.'.$encodedPayload, $this->secret, true);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new InvalidTokenException('La firma del token no coincide.');
        }

        $expiration = Arr::get($payload, 'exp');

        if ($expiration !== null && now()->getTimestamp() >= (int) $expiration) {
            throw new InvalidTokenException('El token ha expirado.');
        }

        return $payload;
    }

    public function ttl(): int
    {
        return $this->ttl;
    }

    protected function encode(array $payload): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];

        $encodedHeader = $this->base64UrlEncode($this->jsonEncode($header));
        $encodedPayload = $this->base64UrlEncode($this->jsonEncode($payload));
        $signature = hash_hmac('sha256', $encodedHeader.'.'.$encodedPayload, $this->secret, true);
        $encodedSignature = $this->base64UrlEncode($signature);

        return implode('.', [$encodedHeader, $encodedPayload, $encodedSignature]);
    }

    protected function jsonEncode(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidTokenException('No se pudo serializar el token.', 0, $exception);
        }
    }

    protected function decodeJsonSegment(string $segment): array
    {
        try {
            $json = $this->base64UrlDecode($segment);

            return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidTokenException('El token tiene un formato inválido.', 0, $exception);
        }
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string
    {
        $padding = strlen($data) % 4;

        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
