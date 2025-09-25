<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InmuebleImage extends Model
{
    use HasFactory;

    protected $table = 'inmueble_imagenes';

    protected $fillable = [
        'inmueble_id',
        'disk',
        'path',
        'url',
        'orden',
        'metadata',
    ];

    protected $casts = [
        'orden' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Image belongs to an inmueble.
     */
    public function inmueble(): BelongsTo
    {
        return $this->belongsTo(Inmueble::class);
    }

    /**
     * Retrieve the URL using the configured filesystem disk when not stored.
     */
    public function getUrlAttribute(?string $value): ?string
    {
        if (! empty($value)) {
            return $value;
        }

        if (empty($this->path) || empty($this->disk)) {
            return $value;
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
