<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IndexBotUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'max:32'],
            'bot_status' => ['nullable', 'string', 'max:32'],
            'questionnaire_status' => ['nullable', 'string', 'max:32'],
            'session_id' => ['nullable', 'string', 'max:32'],
            'telefono_real' => ['nullable', 'string', 'max:20'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
