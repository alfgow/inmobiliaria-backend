<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contactos';

    protected $fillable = [
        'inmueble_id',
        'nombre',
        'email',
        'telefono',
        'mensaje',
        'estado',
        'fuente',
    ];
}
