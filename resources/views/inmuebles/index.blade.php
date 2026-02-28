<x-layouts.admin title="Inmuebles">
    {{-- Header --}}
    <header class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-3xl font-black text-slate-900">Propiedades</h2>
            <a href="{{ route('inmuebles.create') }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-blue-200 transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva Propiedad</span>
            </a>
        </div>
    </header>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $metrics['total'] }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-xs text-blue-600 font-medium uppercase tracking-wider">Destacados</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $metrics['destacados'] }}</p>
        </div>
        @foreach ($operationBreakdown as $operation => $count)
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">{{ $operation }}</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $count }}</p>
            </div>
        @endforeach
        @if ($operationBreakdown->isEmpty())
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Operaciones</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">0</p>
            </div>
        @endif
    </div>

    {{-- Filters --}}
    <form action="{{ route('inmuebles.index') }}" method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-500 mb-2">Buscar</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input type="search" name="search" value="{{ $search }}" placeholder="Título o dirección..." 
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition-all">
                </div>
            </div>
            <div class="w-full lg:w-48">
                <label class="block text-xs font-medium text-slate-500 mb-2">Operación</label>
                <select name="operacion" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition-all appearance-none cursor-pointer">
                    <option value="">Todas</option>
                    @foreach (\App\Models\Inmueble::OPERACIONES as $operacion)
                        <option value="{{ $operacion }}" @selected($selectedOperacion === $operacion)>{{ $operacion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full lg:w-48">
                <label class="block text-xs font-medium text-slate-500 mb-2">Estatus</label>
                <select name="estatus" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition-all appearance-none cursor-pointer">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}" @selected((string) $selectedStatus === (string) $status->id)>
                            {{ $status->nombre }} ({{ $status->inmuebles_count }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('inmuebles.index') }}" class="px-4 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-sm font-medium transition-colors">
                    Limpiar
                </a>
            </div>
        </div>
    </form>

    {{-- Empty State --}}
    @if ($inmuebles->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">No hay inmuebles</h3>
            <p class="text-slate-500 mb-6">Comienza agregando tu primera propiedad al catálogo.</p>
            <a href="{{ route('inmuebles.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Crear inmueble
            </a>
        </div>
    @else
        {{-- Properties Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($inmuebles as $inmueble)
                <article class="group bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300">
                    {{-- Image --}}
                    <div class="relative h-56 overflow-hidden">
                        @if ($inmueble->coverImage)
                            <img src="{{ $inmueble->coverImage->temporaryVariantUrl('watermarked') ?? $inmueble->coverImage->url }}" alt="{{ $inmueble->titulo }}" 
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                        @endif
                        
                        {{-- Badges --}}
                        <div class="absolute top-3 left-3 flex gap-2">
                            <span class="bg-white/90 backdrop-blur-md text-slate-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                {{ $inmueble->operacion }}
                            </span>
                            @if ($inmueble->destacado)
                                <span class="bg-blue-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                    Destacado
                                </span>
                            @endif
                        </div>

                        {{-- Status Ribbon --}}
                        @php
                            $isAvailableStatus = \App\Support\InmuebleStatusClassifier::isAvailableStatusId($inmueble->estatus_id);
                            $normalizedStatus = \Illuminate\Support\Str::of($inmueble->status->nombre ?? '')->lower()->squish()->value();
                            $isRentadoOVendido = in_array($normalizedStatus, ['rentado', 'vendido'], true);
                        @endphp
                        @if (! $isAvailableStatus)
                            <div class="absolute inset-0 bg-slate-900/40 flex items-center justify-center">
                                <span class="bg-white/95 text-slate-800 px-4 py-2 rounded-full text-sm font-bold shadow-lg">
                                    {{ $inmueble->status->nombre }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="p-5">
                        {{-- Location --}}
                        <p class="text-xs text-blue-600 font-semibold uppercase tracking-wide mb-1">
                            {{ $inmueble->colonia ?? $inmueble->municipio }}
                        </p>
                        
                        {{-- Title --}}
                        <h3 class="text-lg font-bold text-slate-800 line-clamp-1 mb-2" title="{{ $inmueble->titulo }}">
                            {{ $inmueble->titulo }}
                        </h3>

                        {{-- Address --}}
                        @php
                            $fullAddress = collect([$inmueble->direccion, $inmueble->colonia])->filter()->join(', ');
                        @endphp
                        <p class="text-sm text-slate-500 line-clamp-1 mb-3">
                            {{ $fullAddress }}
                        </p>

                        {{-- Price --}}
                        <p class="text-xl font-black text-slate-900 mb-4">
                            {{ $inmueble->formattedPrice() }}
                        </p>

                        {{-- Features --}}
                        <div class="flex items-center gap-4 py-3 border-t border-slate-100 text-slate-500 text-sm">
                            @if ($inmueble->habitaciones)
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 01-2 2v4a2 2 0 012 2h14a2 2 0 012-2v-4a2 2 0 01-2-2m-2-4h.01M17 16h.01" />
                                    </svg>
                                    <span>{{ $inmueble->habitaciones }}</span>
                                </div>
                            @endif
                            @if ($inmueble->banos)
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <span>{{ $inmueble->banos }}</span>
                                </div>
                            @endif
                            @if ($inmueble->metros_cuadrados)
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                    <span>{{ $inmueble->metros_cuadrados }} m²</span>
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <a href="{{ route('inmuebles.edit', $inmueble) }}" class="text-center text-xs font-bold py-2.5 px-4 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                Editar
                            </a>
                            <a href="https://wa.me/?text={{ urlencode('Hola, te comparto esta propiedad: ' . 'https://www.villanuevagarcia.com/inmuebles/' . $inmueble->slug) }}" target="_blank" 
                                class="flex items-center justify-center gap-1 text-xs font-bold py-2.5 px-4 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $inmuebles->withQueryString()->links() }}
        </div>
    @endif
</x-layouts.admin>
