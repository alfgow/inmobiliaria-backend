<x-layouts.admin :title="'Editar inmueble · ' . $inmueble->titulo">
    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-blue-600 dark:text-blue-400">Gestión</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100 md:text-4xl">{{ $inmueble->titulo }}</h1>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            {{ $inmueble->operacion }} · {{ $inmueble->tipo }}
                        </span>
                        <div class="inline-flex items-center gap-3 rounded-full bg-slate-100 px-4 py-1.5 text-slate-700 shadow-sm dark:bg-slate-700 dark:text-slate-300">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Estatus</span>
                            <div class="relative">
                                <select
                                    id="estatus_id"
                                    name="estatus_id"
                                    form="inmueble-update-form"
                                    class="estatus-select bg-transparent border-none text-sm font-medium focus:outline-none cursor-pointer"
                                >
                                    <option value="">Selecciona un estado</option>
                                    @foreach ($statuses as $status)
                                        <option
                                            value="{{ $status->id }}"
                                            data-status-name="{{ $status->nombre }}"
                                            data-status-slug="{{ \Illuminate\Support\Str::slug($status->nombre) }}"
                                            @selected((int) old('estatus_id', $inmueble->estatus_id) === $status->id)
                                        >
                                            {{ $status->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-0 top-1/2 -translate-y-1/2 text-xs font-semibold text-blue-600/80 dark:text-blue-400/80">▾</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-slate-600 dark:bg-slate-700 dark:text-slate-400">
                            Actualizado {{ $inmueble->lastUpdatedDiff() ?? 'recién' }}
                        </span>
                    </div>
                    @error('estatus_id')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('inmuebles.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700">Volver al listado</a>
                    <form action="{{ route('inmuebles.destroy', $inmueble) }}" method="POST" data-swal-confirm data-swal-title="¿Eliminar inmueble?" data-swal-confirm="Se eliminarán también sus fotografías." data-swal-confirm-button="Sí, eliminar" data-swal-cancel-button="Cancelar">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-red-500/40 bg-red-50 px-5 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                            Eliminar inmueble
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-3xl border border-emerald-500/40 bg-emerald-50 px-6 py-4 text-sm text-emerald-700 shadow-sm dark:bg-emerald-900/30 dark:text-emerald-400">
                {{ session('status') }}
            </div>
        @endif

        <form
            id="inmueble-update-form"
            action="{{ route('inmuebles.update', $inmueble) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-8"
            data-swal-loader="actualizar-inmueble"
            data-swal-loader-title="Guardando cambios"
            data-swal-loader-text="Estamos aplicando las actualizaciones del inmueble..."
        >
            @csrf
            @method('PUT')

            <x-inmuebles.form
                :inmueble="$inmueble"
                :statuses="$statuses"
                :tipos="$tipos"
                :operaciones="$operaciones"
                :watermark-preview-url="$watermarkPreviewUrl"
                :show-status-selector="false"
            />

            <div class="flex flex-col items-stretch gap-3 border-t border-slate-200 pt-6 dark:border-slate-700 sm:flex-row sm:justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">Los cambios se guardan en tu historial para futuras referencias.</p>
                <div class="flex gap-3">
                    <a href="{{ route('inmuebles.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700">Cancelar</a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/30">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
