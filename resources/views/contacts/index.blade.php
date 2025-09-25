<x-layouts.admin>
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold mb-2">Contactos</h1>
            <p class="text-gray-400">Busca por nombre, teléfono o correo para verificar si ya está registrado.</p>
        </div>

        <form action="{{ route('contactos.index') }}" method="GET" class="bg-gray-900/50 border border-gray-800 rounded-2xl p-5 shadow-xl">
            <label for="search" class="block text-sm font-medium text-gray-300 mb-2">Buscar contacto</label>
            <div class="flex flex-col sm:flex-row gap-3">
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
            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($contacts as $contact)
                    <article class="rounded-2xl border border-gray-800 bg-gray-900/60 p-5 shadow-lg shadow-black/30">
                        <div class="flex items-center justify-between mb-4">
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
                                <dt class="text-gray-500 mb-1">Mensaje:</dt>
                                <dd class="rounded-xl border border-gray-800 bg-gray-950/60 p-3 text-gray-200">{{ $contact->mensaje ?? '—' }}</dd>
                            </div>
                        </dl>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>

    @if ($search !== '' && $contacts->isEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'info',
                        title: 'Sin resultados',
                        text: 'No encontramos contactos que coincidan con tu búsqueda.',
                        confirmButtonText: 'Entendido',
                    });
                } else {
                    alert('No encontramos contactos que coincidan con tu búsqueda.');
                }
            });
        </script>
    @endif
</x-layouts.admin>
