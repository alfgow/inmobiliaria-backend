<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Inmueble;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $contacts = Contact::query()
            ->with(['latestInterest.inmueble', 'latestComment'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($contactQuery) use ($search) {
                    $contactQuery
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($search === '', function ($query) {
                $query->whereRaw('0 = 1');
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
            'comentario' => ['nullable', 'string'],
        ]);

        $contact = null;

        DB::transaction(function () use (&$contact, $data) {
            $contact = Contact::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'] ?? null,
                'telefono' => $data['telefono'] ?? null,
            ]);

            if (! empty($data['comentario'])) {
                $contact->comentarios()->create([
                    'comentario' => $data['comentario'],
                    'created_at' => now(),
                ]);
                $contact->touch();
            }

            if (! empty($data['inmueble_id'])) {
                $contact->intereses()->create([
                    'inmueble_id' => $data['inmueble_id'],
                    'created_at' => now(),
                ]);
                $contact->touch();
            }
        });

        return redirect()
            ->route('contactos.show', $contact)
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

    public function show(Contact $contact): View
    {
        $contact->load([
            'comentarios' => fn ($query) => $query->orderByDesc('created_at'),
            'intereses' => fn ($query) => $query->with('inmueble')->orderByDesc('created_at'),
        ]);

        return view('contacts.show', [
            'contact' => $contact,
            'inmuebles' => Inmueble::query()
                ->orderBy('titulo')
                ->get(['id', 'titulo', 'direccion', 'operacion', 'tipo']),
        ]);
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contactName = trim((string) $contact->nombre);

        DB::transaction(static function () use ($contact): void {
            $contact->delete();
        });

        $statusMessage = $contactName === ''
            ? 'El contacto se eliminó correctamente.'
            : "El contacto {$contactName} se eliminó correctamente.";

        return redirect()
            ->route('contactos.index')
            ->with('status', $statusMessage);
    }

    public function storeComment(Request $request, Contact $contact): RedirectResponse
    {
        $data = $request->validate([
            'comentario' => ['required', 'string'],
        ]);

        $contact->comentarios()->create([
            'comentario' => $data['comentario'],
            'created_at' => now(),
        ]);

        $contact->touch();

        return redirect()
            ->route('contactos.show', $contact)
            ->with('status', 'El comentario se agregó correctamente.');
    }

    public function storeInterest(Request $request, Contact $contact): RedirectResponse
    {
        $data = $request->validate([
            'inmueble_id' => ['required', 'exists:inmuebles,id'],
        ]);

        $interest = $contact->intereses()->firstOrCreate(
            ['inmueble_id' => $data['inmueble_id']],
            ['created_at' => now()]
        );

        if (! $interest->wasRecentlyCreated) {
            $interest->update(['created_at' => now()]);
        }

        $contact->touch();

        return redirect()
            ->route('contactos.show', $contact)
            ->with('status', 'El inmueble de interés se registró correctamente.');
    }
}
