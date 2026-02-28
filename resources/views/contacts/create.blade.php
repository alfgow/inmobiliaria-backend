@php use Illuminate\Support\Str; @endphp

<x-layouts.admin>
    <div class="flex flex-1 items-center justify-center">
        <div class="w-full max-w-2xl space-y-8">
            <header class="text-center space-y-2">
                <p class="text-sm uppercase tracking-widest text-blue-600 dark:text-blue-400">Nuevo contacto</p>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">Registrar contacto</h1>
                <p class="text-slate-500 dark:text-slate-400">Completa los datos para agregar un nuevo contacto al sistema.</p>
            </header>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <form
                    action="{{ route('contactos.store') }}"
                    method="POST"
                    class="space-y-6"
                    data-swal-loader="registrar-contacto"
                >
                    @csrf

                    <div class="space-y-2">
                        <label for="nombre" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nombre completo<span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="{{ old('nombre', $prefillField === 'nombre' ? $prefill : '') }}"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:placeholder-slate-500 dark:focus:ring-blue-900/50"
                            placeholder="Ej. Juan Pérez"
                        >
                        @error('nombre')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Correo electrónico</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $prefillField === 'email' ? $prefill : '') }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:placeholder-slate-500 dark:focus:ring-blue-900/50"
                            placeholder="correo@dominio.com"
                        >
                        @error('email')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="telefono" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Teléfono</label>
                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            value="{{ old('telefono', $prefillField === 'telefono' ? $prefill : '') }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:placeholder-slate-500 dark:focus:ring-blue-900/50"
                            placeholder="5512345678"
                        >
                        @error('telefono')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="inmueble_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Inmueble de interés</label>
                        <div
                            class="space-y-4"
                            data-searchable-select
                            data-search-placeholder="Buscar por título o dirección"
                        >
                            <select
                                id="inmueble_id"
                                name="inmueble_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:focus:ring-blue-900/50"
                            >
                                <option value="">Sin inmueble registrado</option>
                                @foreach ($inmuebles as $inmueble)
                                    @php
                                        $fullAddress = collect([
                                            $inmueble->direccion,
                                            $inmueble->colonia,
                                            $inmueble->municipio,
                                            $inmueble->estado,
                                        ])->filter()->join(', ');
                                    @endphp
                                    <option
                                        value="{{ $inmueble->id }}"
                                        data-searchable="{{ Str::lower(trim($inmueble->titulo . ' ' . $fullAddress)) }}"
                                        data-cover-image="{{ $inmueble->cover_image_url }}"
                                        data-title="{{ $inmueble->titulo }}"
                                        data-operation="{{ $inmueble->operacion }}"
                                        data-type="{{ $inmueble->tipo }}"
                                        data-full-address="{{ $fullAddress }}"
                                        data-price="{{ $inmueble->precio }}"
                                        data-habitaciones="{{ $inmueble->habitaciones }}"
                                        data-banos="{{ $inmueble->banos }}"
                                        data-estacionamientos="{{ $inmueble->estacionamientos }}"
                                        data-metros-cuadrados="{{ $inmueble->metros_cuadrados }}"
                                        @selected((string) old('inmueble_id') === (string) $inmueble->id)
                                    >
                                        {{ $inmueble->titulo }}@if ($fullAddress !== '') — {{ $fullAddress }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <div
                                data-property-preview
                                class="hidden flex-col gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-900"
                            >
                                <div class="aspect-video w-full overflow-hidden rounded-lg border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
                                    <img
                                        data-property-preview-image
                                        data-placeholder="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                                        src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                                        alt="Vista previa del inmueble seleccionado"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                                <div class="space-y-3">
                                    <div class="space-y-1">
                                        <p data-property-preview-title class="text-lg font-semibold text-slate-800 dark:text-slate-100"></p>
                                        <p data-property-preview-address class="text-sm text-slate-500 dark:text-slate-400"></p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span data-property-preview-operation class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"></span>
                                        <span data-property-preview-type class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700 dark:bg-sky-900/30 dark:text-sky-300"></span>
                                        <span data-property-preview-price class="ml-auto text-base font-semibold text-slate-900 dark:text-slate-100"></span>
                                    </div>
                                    <dl class="grid grid-cols-2 gap-3 text-sm text-slate-600 dark:text-slate-400">
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500">Habitaciones</dt>
                                            <dd data-property-preview-habitaciones class="font-semibold text-slate-800 dark:text-slate-200"></dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500">Baños</dt>
                                            <dd data-property-preview-banos class="font-semibold text-slate-800 dark:text-slate-200"></dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500">Estacionamientos</dt>
                                            <dd data-property-preview-estacionamientos class="font-semibold text-slate-800 dark:text-slate-200"></dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500">Metros cuadrados</dt>
                                            <dd data-property-preview-metros class="font-semibold text-slate-800 dark:text-slate-200"></dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Utiliza el buscador para registrar el inmueble de interés que quedará ligado al historial.</p>
                        @error('inmueble_id')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="comentario" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Comentario inicial</label>
                        <textarea
                            id="comentario"
                            name="comentario"
                            rows="4"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all resize-none dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:placeholder-slate-500 dark:focus:ring-blue-900/50"
                            placeholder="Información adicional"
                        >{{ old('comentario') }}</textarea>
                        @error('comentario')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('contactos.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-md shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/30">
                            Guardar contacto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
