<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Inmueble;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $contacts = Contact::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($contactQuery) use ($search) {
                    $contactQuery
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mensaje', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('contacts.index', [
            'contacts' => $contacts,
            'search' => $search,
            'searchPrefillField' => $this->detectPrefillField($search),
        ]);
    }

    public function create(Request $request): View
    {
        $prefillValue = trim((string) $request->input('prefill'));
        $requestedField = $request->input('prefill_field');
        $prefillField = in_array($requestedField, ['nombre', 'email', 'telefono'], true)
            ? $requestedField
            : $this->detectPrefillField($prefillValue);

        return view('contacts.create', [
            'prefill' => $prefillValue,
            'prefillField' => $prefillField,
            'inmuebles' => Inmueble::query()
                ->orderBy('titulo')
                ->get(['id', 'titulo', 'direccion', 'operacion', 'tipo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'inmueble_id' => ['nullable', 'exists:inmuebles,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'mensaje' => ['nullable', 'string'],
        ]);

        Contact::create($data);

        return redirect()
            ->route('contactos.index')
            ->with('status', 'El contacto se registró correctamente.');
    }

    private function detectPrefillField(string $value): string
    {
        if ($value === '') {
            return 'nombre';
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        $sanitized = preg_replace('/[\s\-().+]/', '', $value);
        if ($sanitized !== null && $sanitized !== '' && ctype_digit($sanitized) && strlen($sanitized) >= 7) {
            return 'telefono';
        }

        return 'nombre';
    }
}
