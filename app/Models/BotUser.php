<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BotUser extends Model
{
    use HasFactory;

    protected $table = 'bot_users';

    protected $primaryKey = 'session_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'session_id',
        'status',
        'api_contact_id',
        'nombre',
        'telefono_real',
        'rol',
        'bot_status',
        'rejected_count',
        'questionnaire_status',
        'property_id',
        'count_outcontext',
        'last_intencion',
        'last_accion',
        'last_bot_reply',
        'veces_pidiendo_nombre',
        'veces_pidiendo_telefono',
    ];

    protected $casts = [
        'api_contact_id' => 'integer',
        'rejected_count' => 'integer',
        'count_outcontext' => 'integer',
        'veces_pidiendo_nombre' => 'integer',
        'veces_pidiendo_telefono' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function chatHistories(): HasMany
    {
        return $this->hasMany(N8nChatHistory::class, 'session_id', 'session_id');
    }

    public function getRouteKeyName(): string
    {
        return 'session_id';
    }
}
