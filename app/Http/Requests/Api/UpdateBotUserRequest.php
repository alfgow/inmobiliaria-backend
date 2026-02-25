<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBotUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'max:32'],
            'api_contact_id' => ['sometimes', 'nullable', 'integer'],
            'nombre' => ['sometimes', 'nullable', 'string', 'max:255'],
            'telefono_real' => ['sometimes', 'nullable', 'string', 'max:20'],
            'rol' => ['sometimes', 'nullable', 'string', 'max:50'],
            'bot_status' => ['sometimes', 'string', 'max:32'],
            'rejected_count' => ['sometimes', 'integer', 'min:0'],
            'questionnaire_status' => ['sometimes', 'string', 'max:32'],
            'current_question_index' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'property_id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'count_outcontext' => ['sometimes', 'integer', 'min:0'],
            'last_intencion' => ['sometimes', 'nullable', 'string', 'max:64'],
            'last_accion' => ['sometimes', 'nullable', 'string', 'max:64'],
            'last_bot_reply' => ['sometimes', 'nullable', 'string'],
            'veces_pidiendo_nombre' => ['sometimes', 'integer', 'min:0'],
            'veces_pidiendo_telefono' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
