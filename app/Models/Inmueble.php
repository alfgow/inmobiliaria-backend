<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inmueble extends Model
{
    use HasFactory;

    protected $table = 'inmuebles';

    protected $fillable = [
        'asesor_id',
        'titulo',
        'descripcion',
        'precio',
        'direccion',
        'tipo',
        'operacion',
        'estatus_id',
    ];
}
