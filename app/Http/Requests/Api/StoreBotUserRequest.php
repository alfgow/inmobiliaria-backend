<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBotUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'max:32', 'unique:bot_users,session_id'],
            'status' => ['nullable', 'string', 'max:32'],
            'api_contact_id' => ['nullable', 'integer'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'telefono_real' => ['nullable', 'string', 'max:20'],
            'rol' => ['nullable', 'string', 'max:50'],
            'bot_status' => ['nullable', 'string', 'max:32'],
            'rejected_count' => ['nullable', 'integer', 'min:0'],
            'questionnaire_status' => ['nullable', 'string', 'max:32'],
            'current_question_index' => ['nullable', 'integer', 'min:0'],
            'property_id' => ['nullable', 'string', 'max:64'],
            'count_outcontext' => ['nullable', 'integer', 'min:0'],
            'last_intencion' => ['nullable', 'string', 'max:64'],
            'last_accion' => ['nullable', 'string', 'max:64'],
            'last_bot_reply' => ['nullable', 'string'],
            'veces_pidiendo_nombre' => ['nullable', 'integer', 'min:0'],
            'veces_pidiendo_telefono' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
