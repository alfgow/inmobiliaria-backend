<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ContactIaInteraction
 */
class ContactIaInteractionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'payload' => $this->payload ?? [],
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
