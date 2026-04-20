<?php

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['api', 'auth.api'])->get('/_test/api-key-protected', function () {
        return response()->json([
            'user_id' => auth()->id(),
        ]);
    });
});

test('dashboard sidebar exposes the api keys link', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee(route('settings.api-keys.index'), false);
    $response->assertSee('API Keys');
});

test('api key can be suspended, reactivated and rotated', function () {
    $user = User::factory()->create();
    $apiKey = $user->apiKeys()->create([
        'name' => 'Integracion CRM',
        'prefix' => 'ABCD-123',
        'key_hash' => hash('sha256', 'ABCD-1234-KEY-ORIGINAL'),
        'status' => ApiKey::STATUS_ACTIVE,
    ]);

    $this->actingAs($user)
        ->patch(route('settings.api-keys.suspend', $apiKey))
        ->assertRedirect(route('settings.api-keys.index'));

    expect($apiKey->fresh()->status)->toBe(ApiKey::STATUS_SUSPENDED)
        ->and($apiKey->fresh()->suspended_at)->not->toBeNull();

    $this->actingAs($user)
        ->patch(route('settings.api-keys.activate', $apiKey))
        ->assertRedirect(route('settings.api-keys.index'));

    expect($apiKey->fresh()->status)->toBe(ApiKey::STATUS_ACTIVE)
        ->and($apiKey->fresh()->suspended_at)->toBeNull();

    $this->actingAs($user)
        ->post(route('settings.api-keys.rotate', $apiKey))
        ->assertRedirect(route('settings.api-keys.index'));

    $user->refresh();
    $rotatedKeys = $user->apiKeys()->orderBy('id')->get();

    expect($rotatedKeys)->toHaveCount(2)
        ->and($apiKey->fresh()->status)->toBe(ApiKey::STATUS_REVOKED)
        ->and($apiKey->fresh()->revoked_at)->not->toBeNull()
        ->and($rotatedKeys->last()->status)->toBe(ApiKey::STATUS_ACTIVE);
});

test('api middleware authenticates active keys and blocks suspended ones', function () {
    $user = User::factory()->create();
    $plainKey = 'TEST-API-KEY-1234567890';
    $apiKey = $user->apiKeys()->create([
        'name' => 'Webhook',
        'prefix' => substr($plainKey, 0, 8),
        'key_hash' => hash('sha256', $plainKey),
        'status' => ApiKey::STATUS_ACTIVE,
    ]);

    $this->withHeader('X-Api-Key', $plainKey)
        ->getJson('/_test/api-key-protected')
        ->assertOk()
        ->assertJson(['user_id' => $user->id]);

    $this->withHeader('Authorization', 'Bearer '.$plainKey)
        ->getJson('/_test/api-key-protected')
        ->assertOk()
        ->assertJson(['user_id' => $user->id]);

    $apiKey->suspend();

    $this->withHeader('X-Api-Key', $plainKey)
        ->getJson('/_test/api-key-protected')
        ->assertUnauthorized()
        ->assertJsonPath('reason', 'La API key está suspendida.');
});
