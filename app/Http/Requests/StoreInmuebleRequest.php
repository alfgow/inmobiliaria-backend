<?php

namespace App\Http\Requests;

use App\Models\Inmueble;
use App\Support\InmuebleStatusClassifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInmuebleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $requiresCommission = $this->shouldRequireCommission();

        return [
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'direccion' => ['required', 'string', 'max:255'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'colonia' => ['nullable', 'string', 'max:120'],
            'municipio' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'max:120'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'tipo' => ['required', Rule::in(Inmueble::TIPOS)],
            'operacion' => ['required', Rule::in(Inmueble::OPERACIONES)],
            'estatus_id' => ['sometimes', 'integer', Rule::exists('inmueble_estatus', 'id')],
            'habitaciones' => ['nullable', 'integer', 'min:0', 'max:50'],
            'banos' => ['nullable', 'integer', 'min:0', 'max:50'],
            'estacionamientos' => ['nullable', 'integer', 'min:0', 'max:50'],
            'metros_cuadrados' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'superficie_construida' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'superficie_terreno' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'anio_construccion' => ['nullable', 'integer', 'min:1800', 'max:' . now()->year],
            'destacado' => ['sometimes', 'boolean'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'tour_virtual_url' => ['nullable', 'url', 'max:255'],
            'amenidades' => ['nullable', 'string', 'max:2000'],
            'extras' => ['nullable', 'string', 'max:2000'],
            'imagenes' => ['nullable', 'array', 'max:10'],
            'imagenes.*' => ['image', 'max:5120'],
            'commission_percentage' => [
                $requiresCommission ? 'required' : 'nullable',
                'numeric',
                'min:0',
            ],
            'commission_amount' => [
                $requiresCommission ? 'required' : 'nullable',
                'numeric',
                'min:0',
            ],
            'commission_status_id' => ['nullable', 'integer'],
            'commission_status_name' => ['nullable', 'string', 'max:160'],
        ];
    }

    protected function shouldRequireCommission(): bool
    {
        return $this->shouldRequireCommissionForStatus($this->input('estatus_id'));
    }

    protected function shouldRequireCommissionForStatus(mixed $statusId): bool
    {
        if ($statusId === null || $statusId === '') {
            return false;
        }

        return InmuebleStatusClassifier::isClosingStatusId((int) $statusId);
    }
}
