<x-layouts.admin :title="'Editar inmueble · ' . $inmueble->titulo">
    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-8">
        <div class="rounded-3xl border border-gray-800 bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950 p-8 text-white shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-indigo-300">Gestión</p>
                    <h1 class="mt-2 text-3xl font-semibold md:text-4xl">{{ $inmueble->titulo }}</h1>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-gray-300">
                        <span class="inline-flex items-center gap-2 rounded-full bg-indigo-500/10 px-3 py-1 text-indigo-200">
                            {{ $inmueble->operacion }} · {{ $inmueble->tipo }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-950/70 px-3 py-1 text-gray-200">
                            Estatus: <span class="font-semibold" style="color: {{ $inmueble->status->color }}">{{ $inmueble->status->nombre }}</span>
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-950/70 px-3 py-1 text-gray-200">
                            Actualizado {{ $inmueble->lastUpdatedDiff() ?? 'recién' }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('inmuebles.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-700 px-5 py-3 text-sm font-medium text-gray-300 transition hover:border-gray-500 hover:text-white">Volver al listado</a>
                    <form action="{{ route('inmuebles.destroy', $inmueble) }}" method="POST" data-swal-confirm data-swal-title="¿Eliminar inmueble?" data-swal-confirm="Se eliminarán también sus fotografías." data-swal-confirm-button="Sí, eliminar" data-swal-cancel-button="Cancelar">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-red-500/40 bg-red-500/10 px-5 py-3 text-sm font-semibold text-red-200 transition hover:bg-red-500/20">
                            Eliminar inmueble
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-3xl border border-emerald-500/40 bg-emerald-500/10 px-6 py-4 text-sm text-emerald-100 shadow-lg shadow-emerald-500/10">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('inmuebles.update', $inmueble) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <x-inmuebles.form :inmueble="$inmueble" :statuses="$statuses" :tipos="$tipos" :operaciones="$operaciones" />

            <div class="flex flex-col items-stretch gap-3 border-t border-gray-800 pt-6 sm:flex-row sm:justify-between">
                <p class="text-sm text-gray-400">Los cambios se guardan en tu historial para futuras referencias.</p>
                <div class="flex gap-3">
                    <a href="{{ route('inmuebles.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-700 px-5 py-3 text-sm font-medium text-gray-300 transition hover:border-gray-500 hover:text-white">Cancelar</a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
