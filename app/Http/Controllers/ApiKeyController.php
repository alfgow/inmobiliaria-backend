<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiKeyRequest;
use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(Request $request): View
    {
        $supportsAllowedIp = $this->supportsAllowedIp();
        $supportsLifecycle = $this->supportsLifecycleManagement();

        $query = $request->user()->apiKeys();

        if ($supportsLifecycle) {
            $query->orderByRaw(
                "case status when ? then 0 when ? then 1 else 2 end",
                [ApiKey::STATUS_ACTIVE, ApiKey::STATUS_SUSPENDED],
            );
        }

        $apiKeys = $query->latest()->get();

        return view('settings.api-keys.index', [
            'apiKeys' => $apiKeys,
            'createdKey' => $request->session()->pull('created_api_key'),
            'status' => session('status'),
            'supportsAllowedIp' => $supportsAllowedIp,
            'supportsLifecycle' => $supportsLifecycle,
        ]);
    }

    public function store(StoreApiKeyRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        $keyPair = ApiKey::generateKeyPair();

        $payload = [
            'name' => $validated['name'],
            'prefix' => $keyPair['prefix'],
            'key_hash' => $keyPair['hash'],
        ];

        if ($this->supportsAllowedIp()) {
            $payload['allowed_ip'] = $validated['allowed_ip'] ?? null;
        }

        if ($this->supportsLifecycleManagement()) {
            $payload['status'] = ApiKey::STATUS_ACTIVE;
        }

        $apiKey = $user->apiKeys()->create($payload);

        return to_route('settings.api-keys.index')
            ->with('status', 'Se creó una nueva API key correctamente.')
            ->with('created_api_key', [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'access_token' => $keyPair['plain'],
                'prefix' => $keyPair['prefix'],
            ]);
    }

    public function suspend(Request $request, ApiKey $apiKey): RedirectResponse
    {
        $this->authorizeOwnership($request, $apiKey);

        if (! $this->supportsLifecycleManagement()) {
            return $this->missingSchemaRedirect('Para suspender API keys primero aplica el SQL de actualización sobre la tabla `api_keys`.');
        }

        if ($apiKey->isRevoked()) {
            return to_route('settings.api-keys.index')
                ->with('status', 'La API key ya fue revocada y no puede suspenderse.');
        }

        if ($apiKey->isSuspended()) {
            return to_route('settings.api-keys.index')
                ->with('status', 'La API key ya estaba suspendida.');
        }

        $apiKey->suspend();

        return to_route('settings.api-keys.index')
            ->with('status', 'La API key se suspendió correctamente.');
    }

    public function activate(Request $request, ApiKey $apiKey): RedirectResponse
    {
        $this->authorizeOwnership($request, $apiKey);

        if (! $this->supportsLifecycleManagement()) {
            return $this->missingSchemaRedirect('Para reactivar API keys primero aplica el SQL de actualización sobre la tabla `api_keys`.');
        }

        if ($apiKey->isRevoked()) {
            return to_route('settings.api-keys.index')
                ->with('status', 'La API key revocada no puede reactivarse. Debes rotarla o generar una nueva.');
        }

        if ($apiKey->isActive()) {
            return to_route('settings.api-keys.index')
                ->with('status', 'La API key ya estaba vigente.');
        }

        $apiKey->activate();

        return to_route('settings.api-keys.index')
            ->with('status', 'La API key volvió a quedar vigente.');
    }

    public function rotate(Request $request, ApiKey $apiKey): RedirectResponse
    {
        $this->authorizeOwnership($request, $apiKey);

        if (! $this->supportsLifecycleManagement()) {
            return $this->missingSchemaRedirect('Para rotar API keys primero aplica el SQL de actualización sobre la tabla `api_keys`.');
        }

        if ($apiKey->isRevoked()) {
            return to_route('settings.api-keys.index')
                ->with('status', 'La API key ya fue revocada. Genera una nueva si necesitas reemplazarla.');
        }

        $newApiKey = null;
        $keyPair = null;

        DB::transaction(function () use ($request, $apiKey, &$newApiKey, &$keyPair): void {
            $keyPair = ApiKey::generateKeyPair();

            $newApiKey = $request->user()->apiKeys()->create([
                'name' => $apiKey->name,
                'prefix' => $keyPair['prefix'],
                'key_hash' => $keyPair['hash'],
                'allowed_ip' => $this->supportsAllowedIp() ? $apiKey->allowed_ip : null,
                'status' => ApiKey::STATUS_ACTIVE,
            ]);

            $apiKey->revoke();
        });

        return to_route('settings.api-keys.index')
            ->with('status', 'La API key se rotó correctamente. La credencial anterior quedó invalidada.')
            ->with('created_api_key', [
                'id' => $newApiKey->id,
                'name' => $newApiKey->name,
                'access_token' => $keyPair['plain'],
                'prefix' => $keyPair['prefix'],
            ]);
    }

    public function destroy(Request $request, ApiKey $apiKey): RedirectResponse
    {
        $this->authorizeOwnership($request, $apiKey);

        $apiKey->delete();

        return to_route('settings.api-keys.index')
            ->with('status', 'La API key se eliminó correctamente.');
    }

    protected function authorizeOwnership(Request $request, ApiKey $apiKey): void
    {
        if ($apiKey->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    protected function supportsAllowedIp(): bool
    {
        return Schema::hasColumn('api_keys', 'allowed_ip');
    }

    protected function supportsLifecycleManagement(): bool
    {
        return Schema::hasColumns('api_keys', ['status', 'suspended_at', 'revoked_at']);
    }

    protected function missingSchemaRedirect(string $message): RedirectResponse
    {
        return to_route('settings.api-keys.index')->with('status', $message);
    }
}
