<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InmuebleRestriction extends Model
{
    use HasFactory;

    protected $table = 'restricciones_inmueble';

    protected $fillable = [
        'id_inmueble',
        'acepta_mascotas',
        'acepta_niños',
        'acepta_estudiantes',
        'acepta_roomies',
        'ingresos_minimos',
        'precio_poliza',
        'requiere_comprobantes_ingresos',
        'observaciones',
    ];

    protected $casts = [
        'ingresos_minimos' => 'decimal:2',
        'precio_poliza' => 'decimal:2',
        'requiere_comprobantes_ingresos' => 'boolean',
    ];

    public function inmueble(): BelongsTo
    {
        return $this->belongsTo(Inmueble::class, 'id_inmueble');
    }
}
