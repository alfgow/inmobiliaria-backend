<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Contact
 */
class ContactResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'estado' => $this->estado,
            'fuente' => $this->fuente,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'last_interaction_at' => optional($this->last_interaction_at)->toIso8601String(),
            'comentarios' => ContactCommentResource::collection($this->whenLoaded('comentarios')),
            'interacciones_ia' => ContactIaInteractionResource::collection($this->whenLoaded('iaInteractions')),
        ];
    }
}
