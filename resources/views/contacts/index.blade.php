<x-layouts.admin>
    {{-- Header --}}
    <header class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-900 dark:text-slate-100">Contactos</h2>
                <p class="text-slate-500 mt-1 dark:text-slate-400">Busca por nombre, teléfono o correo para verificar si ya está registrado.</p>
            </div>
            <a href="{{ route('contactos.create') }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-blue-200 transition-all active:scale-95 dark:shadow-blue-900/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Nuevo Contacto</span>
            </a>
        </div>
    </header>

    {{-- Search Box --}}
    <form action="{{ route('contactos.index') }}" method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6 dark:bg-slate-800 dark:border-slate-700">
        <label class="block text-sm font-medium text-slate-700 mb-3 dark:text-slate-300">Buscar contacto</label>
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="{{ old('search', $search) }}" 
                    placeholder="Ej. Juan Pérez, 5512345678 o correo@dominio.com"
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition-all dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:placeholder-slate-500 dark:focus:ring-blue-900/50 dark:focus:border-blue-600">
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold text-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <span>Buscar</span>
            </button>
        </div>
    </form>

    {{-- Contacts Grid --}}
    @if ($contacts->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($contacts as $contact)
                <article class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow p-5 dark:bg-slate-800 dark:border-slate-700">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg dark:bg-blue-900/30 dark:text-blue-400">
                                {{ strtoupper(substr($contact->nombre ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200">{{ $contact->nombre ?? 'Sin nombre' }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">ID: #{{ $contact->id }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded-full dark:text-slate-400 dark:bg-slate-700">
                            {{ optional($contact->created_at)->format('d/m/Y') ?? '—' }}
                        </span>
                    </div>

                    {{-- Contact Info --}}
                    <div class="space-y-3 mb-4">
                        @if($contact->email)
                            <div class="flex items-center gap-3 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-slate-600 truncate dark:text-slate-300">{{ $contact->email }}</span>
                            </div>
                        @endif
                        @if($contact->telefono)
                            <div class="flex items-center gap-3 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="text-slate-600 dark:text-slate-300">{{ $contact->telefono }}</span>
                            </div>
                        @endif
                        @if($contact->latestInterest?->inmueble)
                            <div class="flex items-center gap-3 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span class="text-slate-600 truncate dark:text-slate-300">{{ $contact->latestInterest->inmueble->titulo }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Last Interaction --}}
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-4 pt-3 border-t border-slate-100 dark:text-slate-400 dark:border-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Última interacción: {{ optional($contact->last_interaction_at)->format('d/m/Y H:i') ?? 'Sin registros' }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <a href="{{ route('contactos.show', $contact) }}" class="flex-1 text-center py-2.5 bg-blue-50 text-blue-600 rounded-lg text-sm font-semibold hover:bg-blue-100 transition-colors dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                            Ver detalle
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->telefono ?? '') }}" target="_blank" class="p-2.5 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50" title="WhatsApp">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $contacts->links() }}
        </div>
    @elseif($search !== '')
        {{-- No results --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center dark:bg-slate-800 dark:border-slate-700">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 dark:bg-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2 dark:text-slate-200">No se encontraron contactos</h3>
            <p class="text-slate-500 mb-6 dark:text-slate-400">No encontramos contactos que coincidan con "{{ $search }}"</p>
            <a href="{{ route('contactos.create', ['prefill' => $search, 'prefill_field' => $searchPrefillField ?? 'nombre']) }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Registrar nuevo contacto
            </a>
        </div>
    @endif

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
                        customClass: {
                            popup: 'rounded-2xl dark:bg-slate-800 dark:text-slate-200',
                            confirmButton: 'bg-blue-600 text-white px-4 py-2 rounded-xl',
                            cancelButton: 'border border-slate-200 text-slate-600 px-4 py-2 rounded-xl dark:border-slate-600 dark:text-slate-400'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('contactos.create', ['prefill' => $search, 'prefill_field' => $searchPrefillField ?? 'nombre']) }}";
                        }
                    });
                }
            });
        </script>
    @endif
</x-layouts.admin>
