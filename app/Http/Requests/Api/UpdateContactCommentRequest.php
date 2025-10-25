<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comentario' => ['required', 'string'],
        ];
    }
}
