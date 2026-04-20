<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'name',
        'prefix',
        'key_hash',
        'allowed_ip',
        'status',
        'suspended_at',
        'revoked_at',
        'last_used_at',
    ];

    protected $hidden = [
        'key_hash',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'suspended_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maskedKey(): string
    {
        return sprintf('%s••••••••••', $this->prefix);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SUSPENDED => 'Suspendida',
            self::STATUS_REVOKED => 'Revocada',
            default => 'Vigente',
        };
    }

    public function suspend(): void
    {
        $this->forceFill([
            'status' => self::STATUS_SUSPENDED,
            'suspended_at' => now(),
            'revoked_at' => null,
        ])->saveQuietly();
    }

    public function activate(): void
    {
        $this->forceFill([
            'status' => self::STATUS_ACTIVE,
            'suspended_at' => null,
            'revoked_at' => null,
        ])->saveQuietly();
    }

    public function revoke(): void
    {
        $this->forceFill([
            'status' => self::STATUS_REVOKED,
            'revoked_at' => now(),
            'suspended_at' => null,
        ])->saveQuietly();
    }

    public function markAsUsed(): void
    {
        $now = now();

        if ($this->last_used_at !== null && $this->last_used_at->diffInMinutes($now) < 1) {
            return;
        }

        $this->forceFill(['last_used_at' => $now])->saveQuietly();
    }

    public static function generateKeyPair(): array
    {
        do {
            $plainKey = Str::upper(Str::random(4)).'-'.Str::upper(Str::random(24));
            $hash = hash('sha256', $plainKey);
        } while (static::query()->where('key_hash', $hash)->exists());

        $prefix = substr($plainKey, 0, 8);

        return [
            'plain' => $plainKey,
            'prefix' => $prefix,
            'hash' => $hash,
        ];
    }
}
