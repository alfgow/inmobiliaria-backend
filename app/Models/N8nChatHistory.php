<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class N8nChatHistory extends Model
{
    use HasFactory;

    protected $table = 'n8n_chat_histories';

    protected $fillable = [
        'session_id',
        'message',
    ];

    protected $casts = [
        'message' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'session_id', 'session_id');
    }
}
