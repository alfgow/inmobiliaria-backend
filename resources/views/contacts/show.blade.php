@php use Illuminate\Support\Str; @endphp

<x-layouts.admin>
    {{-- Header --}}
    <header class="mb-8">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 dark:text-slate-500">
                    <span>App</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="{{ route('contactos.index') }}" class="hover:text-blue-600 transition-colors dark:hover:text-blue-400">Contactos</a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-blue-600 dark:text-blue-400">Perfil</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 dark:text-slate-100">{{ $contact->nombre ?? 'Contacto sin nombre' }}</h2>
                <p class="text-slate-500 mt-1 dark:text-slate-400">Gestiona las interacciones, comentarios e inmuebles de interés registrados.</p>
            </div>
            
            <div class="flex items-center gap-2">
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
                    <button type="submit" class="inline-flex items-center justify-center gap-2 bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2.5 rounded-xl font-bold text-sm transition-all active:scale-95 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50" title="Eliminar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>

                <a href="{{ route('contactos.index') }}" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl font-bold text-sm transition-all active:scale-95 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600 dark:hover:bg-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="hidden sm:inline">Volver</span>
                </a>
                
                <a href="{{ route('contactos.edit', $contact) }}" class="inline-flex items-center justify-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2.5 rounded-xl font-bold text-sm transition-all active:scale-95 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span class="hidden sm:inline">Editar</span>
                </a>
                
                <a href="{{ route('contactos.create', ['prefill' => $contact->email ?? $contact->telefono ?? $contact->nombre, 'prefill_field' => $contact->email ? 'email' : ($contact->telefono ? 'telefono' : 'nombre')]) }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-blue-200 transition-all active:scale-95 dark:shadow-blue-900/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Nuevo</span>
                </a>
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 font-medium dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    {{-- Contact Info Card --}}
    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-8 hover:shadow-md transition-shadow dark:bg-slate-800 dark:border-slate-700">
        <div class="flex flex-col md:flex-row md:justify-between gap-6">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl font-black shrink-0 relative dark:bg-blue-900/30 dark:text-blue-400">
                    {{ strtoupper(substr($contact->nombre ?? 'U', 0, 1)) }}
                    @if($contact->fuente === 'WhatsApp Bot' || $contact->fuente === 'WhatsApp')
                        <div class="absolute -bottom-1 -right-1 bg-white p-1 rounded-full shadow-sm dark:bg-slate-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="space-y-3">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $contact->nombre ?? 'Sin nombre' }}</h3>
                            <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2 py-0.5 rounded-md dark:bg-slate-700 dark:text-slate-300">ID: #{{ $contact->id }}</span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 pt-1">
                        @if($contact->email)
                        <div class="flex items-center gap-3 text-sm border-l-2 border-slate-100 pl-3 dark:border-slate-700">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shrink-0 dark:bg-blue-900/30 dark:text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium dark:text-slate-500">Correo electrónico</p>
                                <p class="text-slate-700 font-bold dark:text-slate-300">{{ $contact->email }}</p>
                            </div>
                        </div>
                        @else
                        <div class="flex items-center gap-3 text-sm border-l-2 border-slate-100 pl-3 opacity-50 dark:border-slate-700">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 shrink-0 dark:bg-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium dark:text-slate-500">Correo electrónico</p>
                                <p class="text-slate-500 dark:text-slate-500">—</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($contact->telefono)
                        <div class="flex items-center gap-3 text-sm border-l-2 border-slate-100 pl-3 dark:border-slate-700">
                            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-500 shrink-0 dark:bg-green-900/30 dark:text-green-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium dark:text-slate-500">Teléfono</p>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->telefono) }}" target="_blank" class="text-slate-700 font-bold hover:text-green-600 transition-colors flex items-center gap-1 dark:text-slate-300 dark:hover:text-green-400">
                                    {{ $contact->telefono }}
                                </a>
                            </div>
                        </div>
                        @else
                        <div class="flex items-center gap-3 text-sm border-l-2 border-slate-100 pl-3 opacity-50 dark:border-slate-700">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 shrink-0 dark:bg-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium dark:text-slate-500">Teléfono</p>
                                <p class="text-slate-500 dark:text-slate-500">—</p>
                            </div>
                        </div>
                        @endif
                        
                        <div class="flex items-center gap-3 text-sm border-l-2 border-slate-100 pl-3 dark:border-slate-700">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 shrink-0 dark:bg-indigo-900/30 dark:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium dark:text-slate-500">Fuente</p>
                                <p class="text-slate-700 font-bold capitalize dark:text-slate-300">{{ $contact->fuente ?? 'Web' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col justify-center border-t border-slate-100 pt-4 md:border-t-0 md:pt-0 md:border-l md:pl-6 md:text-right shrink-0 dark:border-slate-700">
                <div class="mb-3">
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-0.5 dark:text-slate-500">Fecha de registro</p>
                    <p class="text-slate-700 font-bold text-sm dark:text-slate-300">{{ optional($contact->created_at)->format('d \d\e M Y, H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-0.5 dark:text-slate-500">Última interacción</p>
                    <p class="text-blue-600 font-bold text-sm dark:text-blue-400">{{ optional($contact->last_interaction_at)->format('d \d\e M Y, H:i') ?? 'Sin registros' }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Layout Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Section: Inmuebles de interés --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 dark:bg-slate-800 dark:border-slate-700">
                <header class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2 dark:text-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Inmuebles de interés
                        </h2>
                        <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">Propiedades que le han interesado a este contacto.</p>
                    </div>
                </header>

                <form
                    action="{{ route('contactos.intereses.store', $contact) }}"
                    method="POST"
                    class="space-y-4 mb-6 border-b border-slate-100 pb-6 dark:border-slate-700"
                    data-swal-loader="registrar-interes"
                >
                    @csrf
                    <div class="space-y-2" data-searchable-select data-search-placeholder="Buscar por título o dirección">
                        <label for="nuevo-inmueble" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Agregar inmueble al historial</label>
                        <select
                            id="nuevo-inmueble"
                            name="inmueble_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-colors dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:focus:ring-blue-900/50"
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
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-200 transition-all active:scale-95 dark:shadow-blue-900/30">
                            Guardar inmueble
                        </button>
                    </div>
                </form>

                <div class="space-y-4">
                    @forelse ($contact->intereses as $interes)
                        <article class="flex items-start gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4 hover:bg-blue-50/50 hover:border-blue-100 transition-colors group dark:bg-slate-900/50 dark:border-slate-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                            <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 dark:bg-blue-900/30 dark:text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-slate-800 text-base truncate dark:text-slate-200">{{ optional($interes->inmueble)->titulo ?? 'Inmueble sin título' }}</h3>
                                @if ($interes->inmueble)
                                    @php
                                        $fullAddress = collect([
                                            $interes->inmueble->direccion,
                                            $interes->inmueble->colonia,
                                            $interes->inmueble->municipio,
                                            $interes->inmueble->estado,
                                        ])->filter()->join(', ');
                                    @endphp
                                    <p class="text-xs text-slate-500 mt-1 line-clamp-1 dark:text-slate-400">
                                        {{ $fullAddress !== '' ? $fullAddress : 'Dirección no disponible' }}
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="inline-flex text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-md uppercase tracking-wide dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ $interes->inmueble->tipo }}
                                        </span>
                                        <span class="inline-flex text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md uppercase tracking-wide dark:bg-emerald-900/30 dark:text-emerald-300">
                                            {{ $interes->inmueble->operacion }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[10px] font-bold text-slate-400 block dark:text-slate-500">{{ optional($interes->created_at)->format('d/m/Y') ?? '—' }}</span>
                                <span class="text-[10px] font-bold text-slate-400 block dark:text-slate-500">{{ optional($interes->created_at)->format('H:i') ?? '—' }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="text-center py-6 px-4 bg-slate-50 rounded-xl border border-slate-100 border-dashed text-sm text-slate-500 dark:bg-slate-900/50 dark:border-slate-700 dark:text-slate-400">
                            Aún no se han registrado inmuebles de interés.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Section: Comentarios --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 h-full flex flex-col dark:bg-slate-800 dark:border-slate-700">
                <header class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2 dark:text-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                            Comentarios
                        </h2>
                        <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">Registra notas sobre el seguimiento del contacto.</p>
                    </div>
                </header>

                <form
                    action="{{ route('contactos.comentarios.store', $contact) }}"
                    method="POST"
                    class="space-y-4 mb-6 pt-1"
                    data-swal-loader="registrar-comentario"
                >
                    @csrf
                    <div class="space-y-2">
                        <textarea
                            id="nuevo-comentario"
                            name="comentario"
                            rows="3"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-colors resize-none dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:placeholder-slate-500 dark:focus:ring-indigo-900/30"
                            placeholder="Añade una nota o comentario aquí..."
                            required
                        ></textarea>
                        @error('comentario')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-200 transition-all active:scale-95 dark:shadow-indigo-900/30">
                            Agregar nota
                        </button>
                    </div>
                </form>

                <div class="space-y-4 flex-1 overflow-y-auto pr-2" style="max-height: 400px;">
                    @forelse ($contact->comentarios as $comentario)
                        <article class="relative flex gap-4 p-4 rounded-xl border border-slate-100 bg-white hover:bg-slate-50 transition-colors shadow-sm dark:bg-slate-900 dark:border-slate-700 dark:hover:bg-slate-800">
                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 font-bold border border-slate-200 dark:bg-slate-700 dark:text-slate-400 dark:border-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 w-full min-w-0">
                                <div class="flex justify-between items-start gap-2 mb-1">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                        {{ $contact->fuente === 'WhatsApp Bot' && str_contains($comentario->comentario, 'RESUMEN INTERACCIONES BOT') ? 'Bot de WhatsApp' : 'Asesor' }}
                                    </p>
                                    <span class="text-[10px] font-bold text-slate-400 shrink-0 dark:text-slate-500">{{ optional($comentario->created_at)->format('d/m/Y H:i') ?? '—' }}</span>
                                </div>
                                <div class="text-sm text-slate-600 whitespace-pre-wrap leading-relaxed {{ str_contains($comentario->comentario, 'RESUMEN INTERACCIONES BOT') ? 'italic p-3 bg-indigo-50/50 rounded-lg text-indigo-900 border border-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-200 dark:border-indigo-800' : 'dark:text-slate-300' }}">{{ $comentario->comentario }}</div>
                            </div>
                        </article>
                    @empty
                        <div class="text-center py-6 px-4 bg-slate-50 rounded-xl border border-slate-100 border-dashed text-sm text-slate-500 dark:bg-slate-900/50 dark:border-slate-700 dark:text-slate-400">
                            Todavía no hay comentarios registrados.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
