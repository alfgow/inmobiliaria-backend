<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Inmueble
 */
class InmuebleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $latitude = $this->latitud !== null ? (float) $this->latitud : null;
        $longitude = $this->longitud !== null ? (float) $this->longitud : null;

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'precio_formateado' => $this->formattedPrice(),
            'direccion' => $this->direccion,
            'colonia' => $this->colonia,
            'municipio' => $this->municipio,
            'estado' => $this->estado,
            'codigo_postal' => $this->codigo_postal,
            'ubicacion' => [
                'latitud' => $latitude,
                'longitud' => $longitude,
            ],
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'tipo' => $this->tipo,
            'operacion' => $this->operacion,
            'estatus' => $this->whenLoaded('status', function () {
                return $this->status?->only(['id', 'nombre', 'color']);
            }),
            'habitaciones' => $this->habitaciones,
            'banos' => $this->banos,
            'estacionamientos' => $this->estacionamientos,
            'metros_cuadrados' => $this->metros_cuadrados,
            'superficie_construida' => $this->superficie_construida,
            'superficie_terreno' => $this->superficie_terreno,
            'anio_construccion' => $this->anio_construccion,
            'destacado' => (bool) $this->destacado,
            'video_url' => $this->video_url,
            'tour_virtual_url' => $this->tour_virtual_url,
            'amenidades' => $this->amenidades ?? [],
            'extras' => $this->extras ?? [],
            'imagen_portada' => InmuebleImageResource::make($this->whenLoaded('coverImage')),
            'imagenes' => InmuebleImageResource::collection($this->whenLoaded('images')),
            'restricciones' => $this->whenLoaded('restricciones', function () {
                if ($this->restricciones === null) {
                    return null;
                }

                return [
                    'acepta_mascotas' => $this->restricciones->acepta_mascotas,
                    'acepta_niños' => $this->restricciones->{'acepta_niños'},
                    'acepta_estudiantes' => $this->restricciones->acepta_estudiantes,
                    'acepta_roomies' => $this->restricciones->acepta_roomies,
                    'ingresos_minimos' => $this->restricciones->ingresos_minimos,
                    'precio_poliza' => $this->restricciones->precio_poliza,
                    'requiere_comprobantes_ingresos' => (bool) $this->restricciones->requiere_comprobantes_ingresos,
                    'observaciones' => $this->restricciones->observaciones,
                ];
            }),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
