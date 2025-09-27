@php use Illuminate\Support\Str; @endphp

<x-layouts.admin>
    <div class="flex flex-1 justify-center">
        <div class="w-full max-w-5xl space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-widest text-indigo-400">Perfil de contacto</p>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-100">{{ $contact->nombre ?? 'Contacto sin nombre' }}</h1>
                    <p class="text-gray-400">Gestiona las interacciones, comentarios e inmuebles de interés registrados.</p>
                </div>
                <div class="flex gap-3">
                    <form
                        action="{{ route('contactos.destroy', $contact) }}"
                        method="POST"
                        class="inline-flex"
                        data-swal-confirm="Esta acción eliminará el contacto y todo su historial."
                        data-swal-title="¿Deseas eliminar el contacto?"
                        data-swal-confirm-button="Sí, eliminar"
                        data-swal-cancel-button="Cancelar"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-red-600/30 transition hover:bg-red-500"
                        >
                            🗑️
                        </button>
                    </form>
                    <a href="{{ route('contactos.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-700 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800/60">
                        ← Volver al directorio
                    </a>
                    <a href="{{ route('contactos.create', ['prefill' => $contact->email ?? $contact->telefono ?? $contact->nombre, 'prefill_field' => $contact->email ? 'email' : ($contact->telefono ? 'telefono' : 'nombre')]) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                        ➕ Nuevo contacto
                    </a>
                </div>
            </div>

            @if (session('status'))
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <section class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6 shadow-xl shadow-black/30">
                <div class="flex flex-col gap-6 md:flex-row md:justify-between">
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">ID contacto</p>
                            <p class="text-lg font-semibold text-indigo-300">#{{ $contact->id }}</p>
                        </div>
                        <dl class="space-y-2 text-sm text-gray-300">
                            <div class="flex items-start gap-3">
                                <dt class="text-gray-500">Correo:</dt>
                                <dd class="flex-1 break-all">{{ $contact->email ?? '—' }}</dd>
                            </div>
                            <div class="flex items-start gap-3">
                                <dt class="text-gray-500">Teléfono:</dt>
                                <dd class="flex-1">{{ $contact->telefono ?? '—' }}</dd>
                            </div>
                            <div class="flex items-start gap-3">
                                <dt class="text-gray-500">Fuente:</dt>
                                <dd class="flex-1">{{ $contact->fuente ?? 'Web' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="space-y-2 text-sm text-gray-400">
                        <p>Registrado el: <span class="font-medium text-gray-200">{{ optional($contact->created_at)->format('d/m/Y H:i') ?? '—' }}</span></p>
                        <p>Última interacción: <span class="font-medium text-gray-200">{{ optional($contact->last_interaction_at)->format('d/m/Y H:i') ?? 'Sin registros' }}</span></p>
                    </div>
                </div>
            </section>

            <section class="grid gap-8 lg:grid-cols-2">
                <div class="space-y-6">
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6 shadow-lg shadow-black/30">
                        <header class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-100">Inmuebles de interés</h2>
                                <p class="text-sm text-gray-400">Agrega propiedades asociadas a este contacto.</p>
                            </div>
                        </header>

                        <form
                            action="{{ route('contactos.intereses.store', $contact) }}"
                            method="POST"
                            class="space-y-4"
                            data-swal-loader="registrar-interes"
                        >
                            @csrf
                            <div class="space-y-2" data-searchable-select>
                                <label for="nuevo-inmueble" class="block text-sm font-medium text-gray-300">Agregar inmueble</label>
                                <input
                                    type="search"
                                    id="nuevo-inmueble-buscar"
                                    data-search-input
                                    placeholder="Buscar por título o dirección"
                                    class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                    autocomplete="off"
                                >
                                <select
                                    id="nuevo-inmueble"
                                    name="inmueble_id"
                                    class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                    required
                                >
                                    <option value="" disabled selected>Selecciona un inmueble</option>
                                    @foreach ($inmuebles as $inmueble)
                                        @php
                                            $fullAddress = collect([
                                                $inmueble->direccion,
                                                $inmueble->colonia,
                                                $inmueble->municipio,
                                                $inmueble->estado,
                                            ])->filter()->join(', ');
                                        @endphp
                                        <option value="{{ $inmueble->id }}" data-searchable="{{ Str::lower(trim($inmueble->titulo . ' ' . $fullAddress)) }}">
                                            {{ $inmueble->titulo }}@if ($fullAddress !== '') — {{ $fullAddress }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('inmueble_id')
                                    <p class="text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                                    Guardar inmueble
                                </button>
                            </div>
                        </form>

                        <div class="mt-6 space-y-4">
                            @forelse ($contact->intereses as $interes)
                                <article class="rounded-xl border border-indigo-500/20 bg-indigo-500/5 p-4">
                                    <h3 class="font-semibold text-indigo-200">{{ optional($interes->inmueble)->titulo ?? 'Inmueble sin título' }}</h3>
                                    <p class="text-sm text-gray-400">Registrado el {{ optional($interes->created_at)->format('d/m/Y H:i') ?? '—' }}</p>
                                    @if ($interes->inmueble)
                                        @php
                                            $fullAddress = collect([
                                                $interes->inmueble->direccion,
                                                $interes->inmueble->colonia,
                                                $interes->inmueble->municipio,
                                                $interes->inmueble->estado,
                                            ])->filter()->join(', ');
                                        @endphp
                                        <p class="mt-2 text-sm text-gray-300">
                                            {{ $fullAddress !== '' ? $fullAddress : 'Dirección no disponible' }} · {{ $interes->inmueble->tipo }} en {{ $interes->inmueble->operacion }}
                                        </p>
                                    @endif
                                </article>
                            @empty
                                <p class="text-sm text-gray-400">Aún no se han registrado inmuebles de interés.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6 shadow-lg shadow-black/30">
                        <header class="mb-4">
                            <h2 class="text-lg font-semibold text-gray-100">Comentarios</h2>
                            <p class="text-sm text-gray-400">Registra notas sobre el seguimiento del contacto.</p>
                        </header>

                        <form
                            action="{{ route('contactos.comentarios.store', $contact) }}"
                            method="POST"
                            class="space-y-4"
                            data-swal-loader="registrar-comentario"
                        >
                            @csrf
                            <div class="space-y-2">
                                <label for="nuevo-comentario" class="block text-sm font-medium text-gray-300">Nuevo comentario</label>
                                <textarea
                                    id="nuevo-comentario"
                                    name="comentario"
                                    rows="4"
                                    class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                    placeholder="Escribe aquí tu nota de seguimiento"
                                    required
                                ></textarea>
                                @error('comentario')
                                    <p class="text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                                    Guardar comentario
                                </button>
                            </div>
                        </form>

                        <div class="mt-6 space-y-4">
                            @forelse ($contact->comentarios as $comentario)
                                <article class="rounded-xl border border-gray-800 bg-gray-950/70 p-4">
                                    <p class="text-sm text-gray-200">{{ $comentario->comentario }}</p>
                                    <p class="mt-2 text-xs uppercase tracking-wide text-gray-500">{{ optional($comentario->created_at)->format('d/m/Y H:i') ?? '—' }}</p>
                                </article>
                            @empty
                                <p class="text-sm text-gray-400">Todavía no hay comentarios registrados.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layouts.admin>
