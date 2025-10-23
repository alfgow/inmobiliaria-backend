<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Inmueble;
use App\Support\InmuebleStatusClassifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        if ($search === '') {
            $contacts = new LengthAwarePaginator(
                collect(),
                0,
                12,
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        } else {
            $contacts = Contact::query()
                ->with(['latestInterest.inmueble', 'latestComment'])
                ->where(function ($contactQuery) use ($search) {
                    $contactQuery
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString();
        }

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

        $inmuebles = Inmueble::query()
            ->with('coverImage')
            ->orderBy('titulo')
            ->get([
                'id',
                'titulo',
                'direccion',
                'operacion',
                'tipo',
                'colonia',
                'municipio',
                'estado',
                'precio',
                'habitaciones',
                'banos',
                'estacionamientos',
                'metros_cuadrados',
                'estatus_id',
            ])
            ->filter(static function (Inmueble $inmueble) {
                return InmuebleStatusClassifier::isAvailableStatusId($inmueble->estatus_id);
            })
            ->map(function (Inmueble $inmueble) {
                $coverImage = $inmueble->coverImage;

                $inmueble->setAttribute(
                    'cover_image_url',
                    $coverImage?->temporaryVariantUrl('watermarked') ?? $coverImage?->url
                );

                return $inmueble;
            })
            ->values();

        return view('contacts.create', [
            'prefill' => $prefillValue,
            'prefillField' => $prefillField,
            'inmuebles' => $inmuebles,
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

    public function edit(Contact $contact): View
    {
        return view('contacts.edit', [
            'contact' => $contact,
        ]);
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $contact->fill([
            'nombre' => $data['nombre'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
        ]);

        if ($contact->isDirty()) {
            $contact->save();
        }

        return redirect()
            ->route('contactos.show', $contact)
            ->with('status', 'El contacto se actualizó correctamente.');
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
