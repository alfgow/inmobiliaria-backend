<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contactos';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'estado',
        'fuente',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function comentarios(): HasMany
    {
        return $this->hasMany(ContactComment::class, 'contacto_id');
    }

    public function intereses(): HasMany
    {
        return $this->hasMany(ContactInterest::class, 'contacto_id');
    }

    public function latestComment(): HasOne
    {
        return $this->hasOne(ContactComment::class, 'contacto_id')->latestOfMany();
    }

    public function latestInterest(): HasOne
    {
        return $this->hasOne(ContactInterest::class, 'contacto_id')->latestOfMany();
    }

    public function getLastInteractionAtAttribute(): ?Carbon
    {
        $timestamps = collect([
            $this->updated_at,
            optional($this->latestComment)->created_at,
            optional($this->latestInterest)->created_at,
        ])->filter();

        if ($timestamps->isEmpty()) {
            return null;
        }

        return $timestamps->max();
    }
}
