<x-layouts.admin title="Inmuebles">
    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-10">
        <div class="rounded-3xl border border-gray-800 bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950 p-8 text-white shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-indigo-300">Portafolio</p>
                    <h1 class="mt-2 text-3xl font-semibold md:text-4xl">Inmuebles disponibles</h1>
                    <p class="mt-3 max-w-3xl text-sm text-gray-200 md:text-base">
                        Administra tu inventario con filtros inteligentes y una vista pensada para móviles. Mantén el catálogo siempre listo para compartir con tus prospectos.
                    </p>
                </div>
                <a href="{{ route('inmuebles.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                    Nuevo inmueble
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-3xl border border-emerald-500/40 bg-emerald-500/10 px-6 py-4 text-sm text-emerald-100 shadow-lg shadow-emerald-500/10">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-gray-800 bg-gray-900/70 p-6 shadow-lg shadow-black/20">
                <p class="text-xs uppercase tracking-widest text-gray-500">Total inmuebles</p>
                <p class="mt-2 text-3xl font-semibold text-white">{{ $metrics['total'] }}</p>
            </div>
            <div class="rounded-3xl border border-gray-800 bg-gray-900/70 p-6 shadow-lg shadow-indigo-900/20">
                <p class="text-xs uppercase tracking-widest text-indigo-300">Destacados</p>
                <p class="mt-2 text-3xl font-semibold text-indigo-200">{{ $metrics['destacados'] }}</p>
            </div>
            @foreach ($operationBreakdown as $operation => $count)
                <div class="rounded-3xl border border-gray-800 bg-gray-900/70 p-6 shadow-lg shadow-black/20">
                    <p class="text-xs uppercase tracking-widest text-gray-500">{{ $operation }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ $count }}</p>
                </div>
            @endforeach
            @if ($operationBreakdown->isEmpty())
                <div class="rounded-3xl border border-gray-800 bg-gray-900/70 p-6 shadow-lg shadow-black/20">
                    <p class="text-xs uppercase tracking-widest text-gray-500">Operaciones</p>
                    <p class="mt-2 text-3xl font-semibold text-white">0</p>
                </div>
            @endif
        </div>

        <form action="{{ route('inmuebles.index') }}" method="GET" class="rounded-3xl border border-gray-800 bg-gray-900/60 p-6 shadow-xl shadow-black/20">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="space-y-2">
                    <label for="search" class="text-sm font-medium text-gray-300">Buscar por título o dirección</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Ej. Penthouse en Polanco"
                        class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                    >
                </div>
                <div class="space-y-2">
                    <label for="operacion" class="text-sm font-medium text-gray-300">Operación</label>
                    <select
                        id="operacion"
                        name="operacion"
                        class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                    >
                        <option value="">Todas</option>
                        @foreach (\App\Models\Inmueble::OPERACIONES as $operacion)
                            <option value="{{ $operacion }}" @selected($selectedOperacion === $operacion)>{{ $operacion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="estatus" class="text-sm font-medium text-gray-300">Estatus</label>
                    <select
                        id="estatus"
                        name="estatus"
                        class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                    >
                        <option value="">Todos</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" @selected((string) $selectedStatus === (string) $status->id)>
                                {{ $status->nombre }} ({{ $status->inmuebles_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="w-full rounded-2xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('inmuebles.index') }}" class="hidden rounded-2xl border border-gray-700 px-6 py-3 text-sm font-medium text-gray-300 transition hover:border-gray-500 hover:text-white md:inline-flex md:items-center md:justify-center">Limpiar</a>
                </div>
            </div>
        </form>

        @if ($inmuebles->isEmpty())
            <div class="rounded-3xl border border-gray-800 bg-gray-900/70 p-10 text-center shadow-xl shadow-black/30">
                <p class="text-lg font-semibold text-gray-200">Aún no has registrado inmuebles.</p>
                <p class="mt-2 text-sm text-gray-400">Comienza creando tu primer inmueble para compartirlo con clientes.</p>
                <a href="{{ route('inmuebles.create') }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                    Crear inmueble
                </a>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($inmuebles as $inmueble)
                    <article class="flex flex-col overflow-hidden rounded-3xl border border-gray-800 bg-gray-900/70 shadow-xl shadow-black/30 transition hover:-translate-y-1 hover:border-indigo-500/60">
                        <div class="relative h-56 w-full overflow-hidden">
                            @if ($inmueble->coverImage)
                                <img src="{{ $inmueble->coverImage->temporaryVariantUrl('watermarked') ?? $inmueble->coverImage->url }}" alt="{{ $inmueble->titulo }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-800 to-gray-900 text-gray-500">
                                    <span class="text-4xl">🏠</span>
                                </div>
                            @endif
                            @if ($inmueble->destacado)
                                <span class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-full bg-amber-500/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-900">Destacado</span>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col gap-5 p-6">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-wide text-gray-400">
                                    <span class="rounded-full bg-gray-800/80 px-3 py-1">{{ $inmueble->operacion }}</span>
                                    <span class="rounded-full bg-gray-800/80 px-3 py-1">{{ $inmueble->tipo }}</span>
                                </div>
                                <h2 class="text-xl font-semibold text-white">{{ $inmueble->titulo }}</h2>
                                @php
                                    $location = collect([$inmueble->colonia, $inmueble->municipio, $inmueble->estado])->filter()->join(', ');
                                @endphp
                                <p class="text-sm text-gray-400">{{ $inmueble->direccion }}@if ($location){{ ', ' . $location }}@endif</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-300">
                                <span class="text-lg font-semibold text-indigo-300">{{ $inmueble->formattedPrice() }}</span>
                                @if ($inmueble->habitaciones)
                                    <span>🛏 {{ $inmueble->habitaciones }}</span>
                                @endif
                                @if ($inmueble->banos)
                                    <span>🛁 {{ $inmueble->banos }}</span>
                                @endif
                                @if ($inmueble->estacionamientos)
                                    <span>🚗 {{ $inmueble->estacionamientos }}</span>
                                @endif
                                @if ($inmueble->metros_cuadrados)
                                    <span>📐 {{ $inmueble->metros_cuadrados }} m²</span>
                                @endif
                            </div>

                            @if (filled($inmueble->amenidades))
                                <ul class="flex flex-wrap gap-2 text-xs text-gray-400">
                                    @foreach (collect($inmueble->amenidades)->take(4) as $amenidad)
                                        <li class="rounded-full bg-gray-850/80 px-3 py-1">{{ $amenidad }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="mt-auto flex items-center justify-between">
                                <span class="inline-flex items-center gap-2 rounded-full bg-gray-800/80 px-3 py-1 text-xs font-medium text-gray-200">
                                    <span class="inline-block h-2 w-2 rounded-full" style="background-color: {{ $inmueble->status->color }}"></span>
                                    {{ $inmueble->status->nombre }}
                                </span>
                                <a href="{{ route('inmuebles.edit', $inmueble) }}" class="inline-flex items-center gap-2 rounded-2xl border border-indigo-500/60 px-4 py-2 text-sm font-medium text-indigo-200 transition hover:bg-indigo-500/10">
                                    Gestionar
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pt-6">
                {{ $inmuebles->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
