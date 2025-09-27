@props([
    'inmueble' => null,
    'statuses' => collect(),
    'showStatusSelector' => true,
    'tipos' => [],
    'operaciones' => [],
    'watermarkPreviewUrl' => null,
])

@php
    $amenidadesText = old('amenidades', optional($inmueble)->amenidadesAsText());
    $extrasText = old('extras', optional($inmueble)->extras ? collect($inmueble->extras)->join(PHP_EOL) : '');
    $selectedCodigoPostal = old('codigo_postal', optional($inmueble)->codigo_postal);
    $selectedColonia = old('colonia', optional($inmueble)->colonia);
    $selectedMunicipio = old('municipio', optional($inmueble)->municipio);
    $selectedEstado = old('estado', optional($inmueble)->estado);
@endphp

<div class="space-y-8">
    <section class="rounded-3xl border border-gray-800 bg-gray-900/60 p-6 shadow-xl shadow-black/30">
        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold">Información general</h2>
                <p class="text-sm text-gray-400">Agrega los datos principales del inmueble que se mostrarán en la ficha pública.</p>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="space-y-2">
                    <label for="titulo" class="text-sm font-medium">Título *</label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        value="{{ old('titulo', optional($inmueble)->titulo) }}"
                        placeholder="Ej. Departamento moderno con terraza"
                        class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        required
                    >
                    @error('titulo')
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="precio" class="text-sm font-medium">Precio *</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">$</span>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="precio"
                            name="precio"
                            value="{{ old('precio', optional($inmueble)->precio) }}"
                            class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 pl-7 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            required
                        >
                    </div>
                    @error('precio')
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="space-y-2">
                    <label for="direccion" class="text-sm font-medium">Dirección *</label>
                    <input
                        type="text"
                        id="direccion"
                        name="direccion"
                        value="{{ old('direccion', optional($inmueble)->direccion) }}"
                        placeholder="Calle y número"
                        class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        required
                    >
                    @error('direccion')
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div
                    class="grid grid-cols-1 gap-4"
                    data-postal-selector
                    data-postal-options-url="{{ url('/codigos-postales') }}"
                >
                    <div class="space-y-2">
                        <label for="codigo_postal" class="text-sm font-medium">C.P.</label>
                        <div class="space-y-2" data-searchable-select>
                            <input
                                type="search"
                                id="codigo-postal-search"
                                data-search-input
                                placeholder="Buscar C.P."
                                class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                autocomplete="off"
                            >
                            <select
                                id="codigo_postal"
                                name="codigo_postal"
                                class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            >
                                <option value="">Selecciona una opción</option>
                                @if ($selectedCodigoPostal)
                                    <option
                                        value="{{ $selectedCodigoPostal }}"
                                        data-searchable="{{ strtolower($selectedCodigoPostal) }}"
                                        selected
                                    >
                                        {{ $selectedCodigoPostal }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        @error('codigo_postal')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="colonia" class="text-sm font-medium">Colonia</label>
                        <div class="space-y-2" data-searchable-select>
                            <input
                                type="search"
                                id="colonia-search"
                                data-search-input
                                placeholder="Buscar colonia"
                                class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                autocomplete="off"
                            >
                            <select
                                id="colonia"
                                name="colonia"
                                class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            >
                                <option value="">Selecciona una opción</option>
                                @if ($selectedColonia)
                                    <option
                                        value="{{ $selectedColonia }}"
                                        data-searchable="{{ strtolower($selectedColonia) }}"
                                        selected
                                    >
                                        {{ $selectedColonia }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        @error('colonia')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="municipio" class="text-sm font-medium">Municipio</label>
                        <div class="space-y-2" data-searchable-select>
                            <input
                                type="search"
                                id="municipio-search"
                                data-search-input
                                placeholder="Buscar municipio"
                                class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                autocomplete="off"
                            >
                            <select
                                id="municipio"
                                name="municipio"
                                class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            >
                                <option value="">Selecciona una opción</option>
                                @if ($selectedMunicipio)
                                    <option
                                        value="{{ $selectedMunicipio }}"
                                        data-searchable="{{ strtolower($selectedMunicipio) }}"
                                        selected
                                    >
                                        {{ $selectedMunicipio }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        @error('municipio')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="estado" class="text-sm font-medium">Estado</label>
                        <div class="space-y-2" data-searchable-select>
                            <input
                                type="search"
                                id="estado-search"
                                data-search-input
                                placeholder="Buscar estado"
                                class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                autocomplete="off"
                            >
                            <select
                                id="estado"
                                name="estado"
                                class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            >
                                <option value="">Selecciona una opción</option>
                                @if ($selectedEstado)
                                    <option
                                        value="{{ $selectedEstado }}"
                                        data-searchable="{{ strtolower($selectedEstado) }}"
                                        selected
                                    >
                                        {{ $selectedEstado }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        @error('estado')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid gap-5 {{ $showStatusSelector ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
                <div class="space-y-2">
                    <label for="tipo" class="text-sm font-medium">Tipo *</label>
                    <select
                        id="tipo"
                        name="tipo"
                        class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        required
                    >
                        <option value="">Selecciona una opción</option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo', optional($inmueble)->tipo) === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                    @error('tipo')
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="operacion" class="text-sm font-medium">Operación *</label>
                    <select
                        id="operacion"
                        name="operacion"
                        class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        required
                    >
                        <option value="">Selecciona una opción</option>
                        @foreach ($operaciones as $operacion)
                            <option value="{{ $operacion }}" @selected(old('operacion', optional($inmueble)->operacion) === $operacion)>{{ $operacion }}</option>
                        @endforeach
                    </select>
                    @error('operacion')
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                @if ($showStatusSelector)
                    <div class="space-y-2">
                        <label for="estatus_id" class="text-sm font-medium">Estatus *</label>
                        <select
                            id="estatus_id"
                            name="estatus_id"
                            class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            required
                        >
                            <option value="">Selecciona un estado</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" @selected((int) old('estatus_id', optional($inmueble)->estatus_id) === $status->id)>
                                    {{ $status->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('estatus_id')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            <div class="space-y-2">
                <label for="descripcion" class="text-sm font-medium">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="5"
                    class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                    placeholder="Cuenta la historia del inmueble, puntos fuertes y contexto del vecindario"
                >{{ old('descripcion', optional($inmueble)->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-gray-800 bg-gray-900/60 p-6 shadow-xl shadow-black/30">
        <div class="space-y-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Características</h2>
                    <p class="text-sm text-gray-400">Detalle los elementos que ayudan a tomar decisiones rápidas.</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" name="destacado" value="1" @checked(old('destacado', optional($inmueble)->destacado)) class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-indigo-400">
                    Destacar inmueble en listados
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $featureFields = [
                        ['id' => 'habitaciones', 'label' => 'Habitaciones'],
                        ['id' => 'banos', 'label' => 'Baños'],
                        ['id' => 'estacionamientos', 'label' => 'Estacionamientos'],
                        ['id' => 'metros_cuadrados', 'label' => 'Metros cuadrados (m²)', 'step' => '0.01'],
                        ['id' => 'superficie_construida', 'label' => 'Superficie construida (m²)', 'step' => '0.01'],
                        ['id' => 'superficie_terreno', 'label' => 'Superficie terreno (m²)', 'step' => '0.01'],
                        ['id' => 'anio_construccion', 'label' => 'Año de construcción'],
                    ];
                @endphp

                @foreach ($featureFields as $field)
                    <div class="space-y-2">
                        <label for="{{ $field['id'] }}" class="text-sm font-medium">{{ $field['label'] }}</label>
                        <input
                            type="number"
                            @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
                            min="0"
                            id="{{ $field['id'] }}"
                            name="{{ $field['id'] }}"
                            value="{{ old($field['id'], optional($inmueble)->{$field['id']}) }}"
                            class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        >
                        @error($field['id'])
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="space-y-2">
                    <label for="video_url" class="text-sm font-medium">Video del inmueble</label>
                    <input
                        type="url"
                        id="video_url"
                        name="video_url"
                        value="{{ old('video_url', optional($inmueble)->video_url) }}"
                        placeholder="https://www.youtube.com/..."
                        class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                    >
                    @error('video_url')
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="tour_virtual_url" class="text-sm font-medium">Tour virtual</label>
                    <input
                        type="url"
                        id="tour_virtual_url"
                        name="tour_virtual_url"
                        value="{{ old('tour_virtual_url', optional($inmueble)->tour_virtual_url) }}"
                        placeholder="https://my.matterport.com/..."
                        class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                    >
                    @error('tour_virtual_url')
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-gray-800 bg-gray-900/60 p-6 shadow-xl shadow-black/30">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-2">
                <label for="amenidades" class="text-sm font-medium">Amenidades destacadas</label>
                <textarea
                    id="amenidades"
                    name="amenidades"
                    rows="6"
                    placeholder="Escribe cada amenidad en una línea. Ej. Alberca\nRoof garden\nSeguridad 24/7"
                    class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                >{{ $amenidadesText }}</textarea>
                @error('amenidades')
                    <p class="text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="extras" class="text-sm font-medium">Extras o notas internas</label>
                <textarea
                    id="extras"
                    name="extras"
                    rows="6"
                    placeholder="Ideal para registrar detalles logísticos o recordatorios para el equipo"
                    class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                >{{ $extrasText }}</textarea>
                @error('extras')
                    <p class="text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-gray-800 bg-gray-900/60 p-6 shadow-xl shadow-black/30">
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold">Galería</h2>
                <p class="text-sm text-gray-400">Sube hasta 10 fotografías en formato JPG o PNG. Las primeras se mostrarán como portada.</p>
            </div>

            <div class="space-y-3">
                <input
                    type="file"
                    id="imagenes"
                    name="imagenes[]"
                    accept="image/*"
                    multiple
                    data-gallery-input
                    class="sr-only"
                >
                <div
                    class="flex cursor-pointer flex-col gap-6 rounded-2xl border border-dashed border-gray-700 bg-gray-850/70 px-6 py-8 text-center transition focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:ring-offset-2 focus:ring-offset-gray-950 hover:border-indigo-400/60 hover:bg-gray-850/80"
                    data-gallery-dropzone
                    role="button"
                    tabindex="0"
                    aria-controls="imagenes"
                    aria-label="Agregar imágenes a la galería"
                >
                    <div class="flex flex-col items-center justify-center gap-4" data-gallery-empty-state>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-800/80 text-indigo-300">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-15 12.75h13.5A1.5 1.5 0 0 0 20.25 18V6a1.5 1.5 0 0 0-1.5-1.5H5.25A1.5 1.5 0 0 0 3.75 6v12a1.5 1.5 0 0 0 1.5 1.5Zm5.25-3.75h4.5a1.5 1.5 0 0 0 1.29-2.295l-2.25-3.75a1.5 1.5 0 0 0-2.58 0l-2.25 3.75A1.5 1.5 0 0 0 9 15.75Z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-100">Haz clic para seleccionar tus fotos</p>
                    </div>
                    <div class="hidden w-full space-y-3 text-left" data-gallery-previews-wrapper>
                        <p class="text-xs text-gray-400">Arrastra las fotos para cambiar el orden. La primera será la portada.</p>
                        <div
                            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                            data-gallery-previews-container
                            data-gallery-watermark-url="{{ $watermarkPreviewUrl ?? '' }}"
                        >
                            <template data-gallery-preview-template>
                                <div
                                    class="group relative flex cursor-grab flex-col gap-3 rounded-xl border border-gray-800 bg-gray-900/70 p-3 shadow-lg shadow-black/30 transition hover:border-indigo-400/60"
                                    data-gallery-preview
                                >
                                    <div class="absolute right-2 top-2 z-20 flex items-center gap-2">
                                        <span
                                            class="hidden rounded-full bg-indigo-500 px-2 py-0.5 text-[10px] font-semibold text-white shadow-lg shadow-indigo-500/40"
                                            data-gallery-cover-badge
                                        >
                                            Portada
                                        </span>
                                        <button
                                            type="button"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-black/80 text-sm text-red-200 shadow-lg shadow-black/40 transition hover:bg-black/80 hover:text-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400/70 focus-visible:ring-offset-2 focus-visible:ring-offset-black/40"
                                            aria-label="Eliminar imagen"
                                            data-gallery-remove
                                        >
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 8 8m0-8-8 8" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="relative flex h-32 items-center justify-center overflow-hidden rounded-lg bg-gray-850/80 text-sm text-gray-400">
                                        <div data-gallery-loading>Procesando vista previa...</div>
                                        <img
                                            data-gallery-preview-image
                                            alt="Vista previa de la imagen"
                                            class="hidden h-full w-full object-cover"
                                        >
                                        <img
                                            data-gallery-preview-watermark
                                            alt=""
                                            class="hidden pointer-events-none absolute inset-0 h-full w-full select-none object-cover"
                                        >
                                        <p class="hidden absolute inset-x-0 bottom-0 bg-black/70 px-3 py-2 text-[11px] text-red-300" data-gallery-error></p>
                                    </div>

                                    <div class="flex flex-col gap-1 text-left text-xs">
                                        <span class="truncate font-medium text-gray-100" data-gallery-filename></span>
                                        <span class="text-[11px] text-gray-400">Arrastra para reordenar</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 self-center rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-200 transition hover:bg-indigo-500/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/70 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950"
                        data-gallery-add-more
                        aria-disabled="false"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 4v9m0 0 3-3m-3 3-3-3" />
                        </svg>
                        <span data-gallery-add-more-label>Seleccionar imágenes</span>
                    </button>
                    <p class="text-xs text-gray-400 transition-colors" data-gallery-counter>0 de 10 imágenes seleccionadas</p>
                    <p class="text-xs text-gray-500">Formatos permitidos: JPG y PNG</p>
                </div>
                @error('imagenes')
                    <p class="text-sm text-red-400">{{ $message }}</p>
                @enderror
                @error('imagenes.*')
                    <p class="text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            @if ($inmueble && $inmueble->images->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($inmueble->images as $imagen)
                        <label class="group relative block overflow-hidden rounded-2xl border border-gray-800 bg-gray-900/80 shadow-lg shadow-black/30">
                            <input type="checkbox" name="imagenes_eliminar[]" value="{{ $imagen->id }}" class="absolute right-3 top-3 h-4 w-4 rounded border-gray-600 bg-gray-800 text-red-500 focus:ring-red-400">
                            <img src="{{ $imagen->temporaryVariantUrl('watermarked') ?? $imagen->url }}" alt="Imagen inmueble" class="h-48 w-full object-cover transition duration-300 group-hover:scale-105">
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-3 text-sm text-gray-200">
                                Marcar para eliminar
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
