<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ContactComment
 */
class ContactCommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'comentario' => $this->comentario,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
