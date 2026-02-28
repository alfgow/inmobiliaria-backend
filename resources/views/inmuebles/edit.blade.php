<x-layouts.admin :title="'Editar inmueble · ' . $inmueble->titulo">
    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6">
        {{-- Header Hero --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            {{-- Decorative gradient line --}}
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-violet-500"></div>
            
            <div class="p-6 lg:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    {{-- Left: Title & Info --}}
                    <div class="flex-1 space-y-4">
                        {{-- Breadcrumb pill --}}
                        <div class="inline-flex">
                            <span class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-blue-700 ring-1 ring-blue-200/50 dark:from-blue-900/20 dark:to-indigo-900/20 dark:text-blue-300 dark:ring-blue-800/30">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Gestión de inmuebles
                            </span>
                        </div>

                        {{-- Title --}}
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 lg:text-3xl">
                                {{ $inmueble->titulo }}
                            </h1>
                        </div>

                        {{-- Info Pills Row --}}
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Operation Type Pill --}}
                            <span class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-100 px-4 py-1.5 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:ring-indigo-800/30">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                {{ $inmueble->operacion }} · {{ $inmueble->tipo }}
                            </span>

                            {{-- Status Selector Pill --}}
                            <div class="relative inline-flex items-center rounded-full bg-slate-100 px-2 py-1 ring-1 ring-slate-200 dark:bg-slate-700 dark:ring-slate-600">
                                <span class="pl-3 pr-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estatus</span>
                                <div class="relative">
                                    <select
                                        id="estatus_id"
                                        name="estatus_id"
                                        form="inmueble-update-form"
                                        class="estatus-select cursor-pointer appearance-none border-none bg-transparent py-1 pr-7 pl-2 text-sm font-semibold text-slate-700 focus:outline-none dark:text-slate-200"
                                    >
                                        <option value="">Selecciona</option>
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
                                    <span class="pointer-events-none absolute right-1 top-1/2 -translate-y-1/2 text-slate-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            {{-- Last Updated Pill --}}
                            <span class="inline-flex items-center justify-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-400 dark:ring-slate-600">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $inmueble->lastUpdatedDiff() ?? 'recién' }}
                            </span>
                        </div>

                        @error('estatus_id')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Right: Actions --}}
                    <div class="flex flex-wrap items-center gap-2 lg:flex-col lg:items-end">
                        <a href="{{ route('inmuebles.index') }}" 
                           class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:bg-slate-600 dark:hover:text-slate-100 dark:focus:ring-slate-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver
                        </a>
                        <form action="{{ route('inmuebles.destroy', $inmueble) }}" method="POST" data-swal-confirm data-swal-title="¿Eliminar inmueble?" data-swal-confirm="Se eliminarán también sus fotografías." data-swal-confirm-button="Sí, eliminar" data-swal-cancel-button="Cancelar">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 shadow-sm transition-all duration-200 hover:border-red-300 hover:bg-red-100 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-200 dark:border-red-800/30 dark:bg-red-900/20 dark:text-red-400 dark:hover:border-red-700/30 dark:hover:bg-red-900/30 dark:hover:text-red-300 dark:focus:ring-red-900/30">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Success Message --}}
        @if (session('status'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm dark:border-emerald-800/30 dark:bg-emerald-900/20">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-800/30">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('status') }}</p>
            </div>
        @endif

        {{-- Main Form --}}
        <form
            id="inmueble-update-form"
            action="{{ route('inmuebles.update', $inmueble) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6"
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

            {{-- Form Actions Footer --}}
            <div class="sticky bottom-4 z-30 rounded-2xl border border-slate-200 bg-white/95 px-6 py-4 shadow-lg backdrop-blur supports-[backdrop-filter]:bg-white/80 dark:border-slate-700 dark:bg-slate-800/95 dark:supports-[backdrop-filter]:bg-slate-800/80">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Los cambios se guardarán en el historial
                        </span>
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('inmuebles.index') }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200 sm:flex-none dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:bg-slate-600 dark:hover:text-slate-100 dark:focus:ring-slate-600">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition-all duration-200 hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl hover:shadow-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:shadow-blue-900/30 dark:hover:shadow-blue-900/40 dark:focus:ring-offset-slate-800 sm:flex-none">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
