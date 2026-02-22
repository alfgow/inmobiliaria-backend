<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IndexN8nChatHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['nullable', 'string', 'max:32', 'exists:bot_users,session_id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
