<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactIaInteraction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'interacciones_ia';

    protected $fillable = [
        'contacto_id',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contacto_id');
    }
}
