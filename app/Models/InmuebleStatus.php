<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InmuebleStatus extends Model
{
    use HasFactory;

    protected $table = 'inmueble_estatus';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * All properties assigned to this status.
     */
    public function inmuebles(): HasMany
    {
        return $this->hasMany(Inmueble::class, 'estatus_id');
    }
}
