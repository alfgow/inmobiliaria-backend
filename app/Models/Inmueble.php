<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Inmueble extends Model
{
    use HasFactory;

    public const TIPOS = [
        'Departamento',
        'Casa',
        'Oficina',
        'Local Comercial',
        'Terreno',
        'Bodega',
        'Otro',
    ];

    public const OPERACIONES = [
        'Renta',
        'Venta',
        'Traspaso',
    ];

    protected $table = 'inmuebles';

    protected $fillable = [
        'asesor_id',
        'titulo',
        'descripcion',
        'precio',
        'direccion',
        'ciudad',
        'estado',
        'codigo_postal',
        'tipo',
        'operacion',
        'estatus_id',
        'habitaciones',
        'banos',
        'estacionamientos',
        'metros_cuadrados',
        'superficie_construida',
        'superficie_terreno',
        'anio_construccion',
        'destacado',
        'video_url',
        'tour_virtual_url',
        'amenidades',
        'extras',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'habitaciones' => 'integer',
        'banos' => 'integer',
        'estacionamientos' => 'integer',
        'metros_cuadrados' => 'decimal:2',
        'superficie_construida' => 'decimal:2',
        'superficie_terreno' => 'decimal:2',
        'anio_construccion' => 'integer',
        'destacado' => 'boolean',
        'amenidades' => 'array',
        'extras' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Inmueble belongs to an asesor (user).
     */
    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    /**
     * Inmueble belongs to a status catalog entry.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(InmuebleStatus::class, 'estatus_id');
    }

    /**
     * All images for the property ordered by the preferred order.
     */
    public function images(): HasMany
    {
        return $this->hasMany(InmuebleImage::class)->orderBy('orden')->orderBy('id');
    }

    /**
     * Convenience relation for the first (cover) image.
     */
    public function coverImage(): HasOne
    {
        return $this->hasOne(InmuebleImage::class)->orderBy('orden')->orderBy('id');
    }

    /**
     * Amenidades formatted as newline separated string.
     */
    public function amenidadesAsText(): string
    {
        $amenidades = collect($this->amenidades);

        if ($amenidades->isEmpty()) {
            return '';
        }

        return $amenidades->join(PHP_EOL);
    }

    /**
     * Human readable price representation.
     */
    public function formattedPrice(): string
    {
        return '$' . number_format((float) $this->precio, 2);
    }

    /**
     * Human readable updated at timestamp.
     */
    public function lastUpdatedDiff(): ?string
    {
        $timestamp = $this->updated_at instanceof Carbon ? $this->updated_at : null;

        return $timestamp?->diffForHumans();
    }
}
