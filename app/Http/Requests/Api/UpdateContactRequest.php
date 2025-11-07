<?php

namespace App\Http\Requests\Api;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contact = $this->route('contact');
        $contactId = $contact instanceof Contact ? $contact->getKey() : $contact;

        $telefonoRule = Rule::unique('contactos', 'telefono');

        if ($contactId !== null) {
            $telefonoRule = $telefonoRule->ignore($contactId);
        }

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30', $telefonoRule],
            'estado' => ['nullable', 'string', 'max:150'],
            'fuente' => ['nullable', 'string', 'max:150'],
        ];
    }
}
