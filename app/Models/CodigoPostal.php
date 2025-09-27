<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoPostal extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'codigos_postales';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'd_codigo',
        'd_asenta',
        'd_mnpio',
        'd_estado',
        'd_ciudad',
    ];
}
