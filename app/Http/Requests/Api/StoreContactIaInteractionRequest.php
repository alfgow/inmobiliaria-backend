<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactIaInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payload' => ['required', 'array'],
            'created_at' => ['sometimes', 'date'],
        ];
    }
}
