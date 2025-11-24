<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactStatusRequest extends FormRequest
{
    public const ESTADOS_VALIDOS = [
        'nuevo',
        'en_contacto',
        'convertido',
        'rechazado',
        'rejected',
        'reject',
        'block',
        'blocked',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', 'string', Rule::in(self::ESTADOS_VALIDOS)],
        ];
    }
}
