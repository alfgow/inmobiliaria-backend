<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiKeyRequest;
use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(Request $request): View
    {
        $apiKeys = $request->user()
            ->apiKeys()
            ->latest()
            ->get();

        return view('settings.api-keys.index', [
            'apiKeys' => $apiKeys,
            'createdKey' => $request->session()->pull('created_api_key'),
            'status' => session('status'),
        ]);
    }

    public function store(StoreApiKeyRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        $keyPair = ApiKey::generateKeyPair();

        $apiKey = $user->apiKeys()->create([
            'name' => $validated['name'],
            'prefix' => $keyPair['prefix'],
            'key_hash' => $keyPair['hash'],
            'allowed_ip' => $validated['allowed_ip'] ?? null,
        ]);

        return to_route('settings.api-keys.index')
            ->with('status', 'Se creó una nueva API key correctamente.')
            ->with('created_api_key', [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'token_type' => 'Bearer',
                'access_token' => $keyPair['plain'],
                'expires_in' => config('jwt.ttl'),
                'prefix' => $keyPair['prefix'],
            ]);
    }

    public function destroy(Request $request, ApiKey $apiKey): RedirectResponse
    {
        $this->authorizeOwnership($request, $apiKey);

        $apiKey->delete();

        return to_route('settings.api-keys.index')
            ->with('status', 'La API key se revocó correctamente.');
    }

    protected function authorizeOwnership(Request $request, ApiKey $apiKey): void
    {
        if ($apiKey->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
