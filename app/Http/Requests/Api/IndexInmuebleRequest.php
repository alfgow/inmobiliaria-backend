<?php

namespace App\Http\Requests\Api;

use App\Models\Inmueble;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexInmuebleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('search')) {
            $this->merge([
                'search' => trim((string) $this->query('search')),
            ]);
        }

        if ($this->has('destacado')) {
            $this->merge([
                'destacado' => filter_var(
                    $this->query('destacado'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                ),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'operacion' => ['nullable', 'string', Rule::in(Inmueble::OPERACIONES)],
            'estatus' => ['nullable', 'integer', 'exists:inmueble_estatus,id'],
            'destacado' => ['nullable', 'boolean'],
        ];
    }
}
