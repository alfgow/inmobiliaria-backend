<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateInmuebleRequest extends StoreInmuebleRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['estatus_id'] = ['required', 'integer', Rule::exists('inmueble_estatus', 'id')];

        $rules['imagenes'] = ['nullable', 'array', 'max:10'];
        $rules['imagenes.*'] = ['image', 'max:5120'];
        $rules['imagenes_eliminar'] = ['nullable', 'array'];
        $rules['imagenes_eliminar.*'] = [
            'integer',
            Rule::exists('inmueble_imagenes', 'id')->where('inmueble_id', $this->route('inmueble')?->id),
        ];

        return $rules;
    }
}
