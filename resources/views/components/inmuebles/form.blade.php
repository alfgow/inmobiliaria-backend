@props([
    'inmueble' => null,
    'statuses' => collect(),
    'tipos' => [],
    'operaciones' => [],
])

@php
    $amenidadesText = old('amenidades', optional($inmueble)->amenidadesAsText());
    $extrasText = old('extras', optional($inmueble)->extras ? collect($inmueble->extras)->join(PHP_EOL) : '');
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

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="space-y-2">
                        <label for="ciudad" class="text-sm font-medium">Ciudad</label>
                        <input
                            type="text"
                            id="ciudad"
                            name="ciudad"
                            value="{{ old('ciudad', optional($inmueble)->ciudad) }}"
                            class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        >
                        @error('ciudad')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="estado" class="text-sm font-medium">Estado</label>
                        <input
                            type="text"
                            id="estado"
                            name="estado"
                            value="{{ old('estado', optional($inmueble)->estado) }}"
                            class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        >
                        @error('estado')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="codigo_postal" class="text-sm font-medium">C.P.</label>
                        <input
                            type="text"
                            id="codigo_postal"
                            name="codigo_postal"
                            value="{{ old('codigo_postal', optional($inmueble)->codigo_postal) }}"
                            class="w-full rounded-2xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        >
                        @error('codigo_postal')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
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
                    class="block w-full rounded-2xl border border-dashed border-gray-700 bg-gray-850/70 px-4 py-5 text-sm text-gray-300 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                >
                @error('imagenes')
                    <p class="text-sm text-red-400">{{ $message }}</p>
                @enderror
                @error('imagenes.*')
                    <p class="text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div
                class="hidden grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                data-gallery-previews-container
            >
                <template data-gallery-preview-template>
                    <div class="group relative overflow-hidden rounded-2xl border border-gray-800 bg-gray-900/60 shadow-lg shadow-black/30 transition">
                        <div class="flex h-48 items-center justify-center bg-gray-850/80 text-sm text-gray-400" data-gallery-loading>
                            Procesando vista previa...
                        </div>
                        <img
                            data-gallery-preview-image
                            alt="Vista previa de la imagen"
                            class="hidden h-48 w-full object-cover transition duration-300 group-hover:scale-105"
                        >
                        <p class="hidden px-3 pb-3 text-xs text-gray-400" data-gallery-error></p>
                    </div>
                </template>
            </div>

            @if ($inmueble && $inmueble->images->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($inmueble->images as $imagen)
                        <label class="group relative block overflow-hidden rounded-2xl border border-gray-800 bg-gray-900/80 shadow-lg shadow-black/30">
                            <input type="checkbox" name="imagenes_eliminar[]" value="{{ $imagen->id }}" class="absolute right-3 top-3 h-4 w-4 rounded border-gray-600 bg-gray-800 text-red-500 focus:ring-red-400">
                            <img src="{{ $imagen->url }}" alt="Imagen inmueble" class="h-48 w-full object-cover transition duration-300 group-hover:scale-105">
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
