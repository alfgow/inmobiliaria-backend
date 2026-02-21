<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Inmueble
 */
class InmuebleListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'descripcion' => $this->descripcion,
            'precio_formateado' => $this->formattedPrice(),
            'direccion' => $this->direccion,
            'colonia' => $this->colonia,
            'municipio' => $this->municipio,
            'estado' => $this->estado,
            'codigo_postal' => $this->codigo_postal,
            'tipo' => $this->tipo,
            'operacion' => $this->operacion,
            'habitaciones' => $this->habitaciones,
            'banos' => $this->banos,
            'estacionamientos' => $this->estacionamientos,
            'metros_cuadrados' => $this->metros_cuadrados,
        ];
    }
}
