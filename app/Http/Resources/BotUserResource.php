<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BotUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'session_id' => $this->session_id,
            'status' => $this->status,
            'api_contact_id' => $this->api_contact_id,
            'nombre' => $this->nombre,
            'telefono_real' => $this->telefono_real,
            'rol' => $this->rol,
            'bot_status' => $this->bot_status,
            'rejected_count' => $this->rejected_count,
            'questionnaire_status' => $this->questionnaire_status,
            'property_id' => $this->property_id,
            'count_outcontext' => $this->count_outcontext,
            'last_intencion' => $this->last_intencion,
            'last_accion' => $this->last_accion,
            'last_bot_reply' => $this->last_bot_reply,
            'veces_pidiendo_nombre' => $this->veces_pidiendo_nombre,
            'veces_pidiendo_telefono' => $this->veces_pidiendo_telefono,
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
            'chat_histories_count' => $this->whenCounted('chatHistories'),
        ];
    }
}
