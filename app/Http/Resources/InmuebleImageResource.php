<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\InmuebleImage
 */
class InmuebleImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'orden' => $this->orden,
            'url' => $this->url,
            'metadata' => $this->metadata,
        ];
    }
}
