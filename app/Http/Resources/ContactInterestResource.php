<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ContactInterest
 */
class ContactInterestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'inmueble_id' => $this->inmueble_id,
            'inmueble' => new InmuebleResource($this->whenLoaded('inmueble')),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
