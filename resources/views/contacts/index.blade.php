<x-layouts.admin>
    <div class="flex flex-1 items-center justify-center">
        <div class="w-full max-w-4xl space-y-10">
            <div class="space-y-4 text-center">
                <p class="text-sm uppercase tracking-widest text-indigo-400">Directorio</p>
                <h1 class="text-2xl md:text-3xl font-bold">Contactos</h1>
                <p class="text-gray-400">Busca por nombre, teléfono o correo para verificar si ya está registrado.</p>
            </div>

            @if (session('status'))
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('contactos.index') }}" method="GET" class="rounded-2xl border border-gray-800 bg-gray-900/50 p-6 shadow-xl shadow-black/30">
                <label for="search" class="block text-sm font-medium text-gray-300 mb-3">Buscar contacto</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ old('search', $search) }}"
                        placeholder="Ej. Juan Pérez, 5512345678 o correo@dominio.com"
                        class="flex-1 rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                    >
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-500 px-5 py-3 font-medium text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                        🔍
                        <span>Buscar</span>
                    </button>
                </div>
            </form>

            @if ($contacts->count())
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($contacts as $contact)
                        <article class="rounded-2xl border border-gray-800 bg-gray-900/60 p-5 shadow-lg shadow-black/30">
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-gray-500">ID contacto</p>
                                    <p class="font-semibold text-indigo-300">#{{ $contact->id }}</p>
                                </div>
                                <span class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-300">{{ optional($contact->created_at)->format('d/m/Y') ?? '—' }}</span>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-100">{{ $contact->nombre ?? '—' }}</h2>
                            <dl class="mt-4 space-y-3 text-sm text-gray-300">
                                <div class="flex items-start gap-3">
                                    <dt class="text-gray-500">Correo:</dt>
                                    <dd class="flex-1 break-all">{{ $contact->email ?? '—' }}</dd>
                                </div>
                                <div class="flex items-start gap-3">
                                    <dt class="text-gray-500">Teléfono:</dt>
                                    <dd class="flex-1">{{ $contact->telefono ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="mb-1 text-gray-500">Mensaje:</dt>
                                    <dd class="rounded-xl border border-gray-800 bg-gray-950/60 p-3 text-gray-200">{{ $contact->mensaje ?? '—' }}</dd>
                                </div>
                            </dl>
                        </article>
                    @endforeach
                </div>

                <div class="pt-4">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>
    </div>

    @if ($search !== '' && $contacts->isEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'info',
                        title: 'Sin resultados',
                        text: 'No encontramos contactos que coincidan con tu búsqueda.',
                        confirmButtonText: 'Registrar contacto',
                        cancelButtonText: 'Cerrar',
                        showCancelButton: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('contactos.create', ['prefill' => $search, 'prefill_field' => $searchPrefillField]) }}";
                        }
                    });
                } else {
                    if (confirm('No encontramos contactos que coincidan con tu búsqueda. ¿Deseas registrar un nuevo contacto?')) {
                        window.location.href = "{{ route('contactos.create', ['prefill' => $search, 'prefill_field' => $searchPrefillField]) }}";
                    }
                }
            });
        </script>
    @endif
</x-layouts.admin>
