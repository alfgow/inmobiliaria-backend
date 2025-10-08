<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prefix',
        'key_hash',
        'last_used_at',
    ];

    protected $hidden = [
        'key_hash',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maskedKey(): string
    {
        return sprintf('%s••••••••••', $this->prefix);
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
