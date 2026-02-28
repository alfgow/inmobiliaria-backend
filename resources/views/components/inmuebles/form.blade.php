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
    $tags = optional($inmueble)->tags;
    $tagsText = old('tags', is_array($tags) ? collect($tags)->join(', ') : '');
    $selectedCodigoPostal = old('codigo_postal', optional($inmueble)->codigo_postal);
    $selectedColonia = old('colonia', optional($inmueble)->colonia);
    $selectedMunicipio = old('municipio', optional($inmueble)->municipio);
    $selectedEstado = old('estado', optional($inmueble)->estado);

    $formControlClasses = 'w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 placeholder-slate-400 shadow-sm transition duration-200 ease-out focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:placeholder-slate-500 dark:focus:border-blue-500 dark:focus:ring-blue-900/50';
    $selectControlClasses = $formControlClasses . ' pr-10 appearance-none';
    $textareaControlClasses = $formControlClasses . ' min-h-[3rem]';
@endphp

<div class="space-y-10">
    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Información general</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Agrega los datos principales del inmueble que se mostrarán en la ficha pública.</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-12">
                <div class="space-y-3 lg:col-span-7">
                    <label for="titulo" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Título *</label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        value="{{ old('titulo', optional($inmueble)->titulo) }}"
                        placeholder="Ej. Departamento moderno con terraza"
                        class="{{ $formControlClasses }}"
                        required
                    >
                    @error('titulo')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-3 lg:col-span-5">
                    <label for="precio" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Precio *</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400 dark:text-slate-500">$</span>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="precio"
                            name="precio"
                            value="{{ old('precio', optional($inmueble)->precio) }}"
                            class="{{ $formControlClasses }} pl-8"
                            required
                        >
                    </div>
                    @error('precio')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Dirección -->
            <div class="grid gap-6 lg:grid-cols-12">
                <div class="space-y-3 lg:col-span-12">
                    <label for="direccion" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Dirección *</label>
                    <input
                        type="text"
                        id="direccion"
                        name="direccion"
                        value="{{ old('direccion', optional($inmueble)->direccion) }}"
                        placeholder="Calle y número"
                        class="{{ $formControlClasses }}"
                        required
                    >
                    @error('direccion')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-12">
                <div class="space-y-3 lg:col-span-12">
                    <div
                        id="inmueble-map"
                        data-postal-resolve-url="{{ route('codigos-postales.resolve') }}"
                        class="h-64 w-full rounded-3xl border border-slate-200 bg-slate-100 shadow-inner dark:border-slate-600 dark:bg-slate-900"
                    ></div>
                    <input
                        type="hidden"
                        name="latitud"
                        value="{{ old('latitud', optional($inmueble)->latitud) }}"
                    >
                    <input
                        type="hidden"
                        name="longitud"
                        value="{{ old('longitud', optional($inmueble)->longitud) }}"
                    >
                    @error('latitud')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    @error('longitud')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- C.P. + Colonia -->
            <section
                class="space-y-6"
                data-postal-selector
                data-postal-options-url="{{ route('codigos-postales.index') }}"
            >
                <div class="grid gap-6 lg:grid-cols-12">
                    <div class="space-y-3 lg:col-span-6">
                        <label for="codigo_postal" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">C.P.</label>
                        <div
                            class="space-y-2"
                            data-searchable-select
                            data-search-placeholder="Buscar C.P."
                        >
                            <select
                                id="codigo_postal"
                                name="codigo_postal"
                                class="{{ $selectControlClasses }}"
                            >
                                <option value="">Selecciona una opción</option>
                                @if ($selectedCodigoPostal)
                                    <option value="{{ $selectedCodigoPostal }}" selected>{{ $selectedCodigoPostal }}</option>
                                @endif
                            </select>
                        </div>
                        @error('codigo_postal')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-3 lg:col-span-6">
                        <label for="colonia" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Colonia</label>
                        <div
                            class="space-y-2"
                            data-searchable-select
                            data-search-placeholder="Buscar colonia"
                        >
                            <select
                                id="colonia"
                                name="colonia"
                                class="{{ $selectControlClasses }}"
                            >
                                <option value="">Selecciona una opción</option>
                                @if ($selectedColonia)
                                    <option value="{{ $selectedColonia }}" selected>{{ $selectedColonia }}</option>
                                @endif
                            </select>
                        </div>
                        @error('colonia')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Municipio + Estado -->
                <div class="grid gap-6 lg:grid-cols-12">
                    <div class="space-y-3 lg:col-span-6">
                        <label for="municipio" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Municipio</label>
                        <div
                            class="space-y-2"
                            data-searchable-select
                            data-search-placeholder="Buscar municipio"
                        >
                            <select
                                id="municipio"
                                name="municipio"
                                class="{{ $selectControlClasses }}"
                            >
                                <option value="">Selecciona una opción</option>
                                @if ($selectedMunicipio)
                                    <option value="{{ $selectedMunicipio }}" selected>{{ $selectedMunicipio }}</option>
                                @endif
                            </select>
                        </div>
                        @error('municipio')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-3 lg:col-span-6">
                        <label for="estado" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Estado</label>
                        <div
                            class="space-y-2"
                            data-searchable-select
                            data-search-placeholder="Buscar estado"
                        >
                            <select
                                id="estado"
                                name="estado"
                                class="{{ $selectControlClasses }}"
                            >
                                <option value="">Selecciona una opción</option>
                                @if ($selectedEstado)
                                    <option value="{{ $selectedEstado }}" selected>{{ $selectedEstado }}</option>
                                @endif
                            </select>
                        </div>
                        @error('estado')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

           
            <div class="grid gap-5 {{ $showStatusSelector ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
                <div class="space-y-3">
                    <label for="tipo" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Tipo *</label>
                    <select
                        id="tipo"
                        name="tipo"
                        class="{{ $selectControlClasses }}"
                        required
                    >
                        <option value="">Selecciona una opción</option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo', optional($inmueble)->tipo) === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                    @error('tipo')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-3">
                    <label for="operacion" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Operación *</label>
                    <select
                        id="operacion"
                        name="operacion"
                        class="{{ $selectControlClasses }}"
                        required
                    >
                        <option value="">Selecciona una opción</option>
                        @foreach ($operaciones as $operacion)
                            <option value="{{ $operacion }}" @selected(old('operacion', optional($inmueble)->operacion) === $operacion)>{{ $operacion }}</option>
                        @endforeach
                    </select>
                    @error('operacion')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                @if ($showStatusSelector)
                    <div class="space-y-3">
                        <label for="estatus_id" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Estatus *</label>
                        <select
                            id="estatus_id"
                            name="estatus_id"
                            class="estatus-select {{ $selectControlClasses }}"
                            required
                        >
                            <option value="">Selecciona un estado</option>
                            @foreach ($statuses as $status)
                                <option
                                    value="{{ $status->id }}"
                                    data-status-name="{{ $status->nombre }}"
                                    data-status-slug="{{ \Illuminate\Support\Str::slug($status->nombre) }}"
                                    @selected((int) old('estatus_id', optional($inmueble)->estatus_id) === $status->id)
                                >
                                    {{ $status->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('estatus_id')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <input
                    type="hidden"
                    id="commission_percentage"
                    name="commission_percentage"
                    value="{{ old('commission_percentage', optional($inmueble)->commission_percentage) }}"
                >
                <input
                    type="hidden"
                    id="commission_amount"
                    name="commission_amount"
                    value="{{ old('commission_amount', optional($inmueble)->commission_amount) }}"
                >
                <input
                    type="hidden"
                    id="commission_status_id"
                    name="commission_status_id"
                    value="{{ old('commission_status_id', optional($inmueble)->commission_status_id) }}"
                >
                <input
                    type="hidden"
                    id="commission_status_name"
                    name="commission_status_name"
                    value="{{ old('commission_status_name', optional($inmueble)->commission_status_name) }}"
                >
            </div>

            <div class="space-y-3">
                <label for="descripcion" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="5"
                    class="{{ $textareaControlClasses }}"
                    placeholder="Cuenta la historia del inmueble, puntos fuertes y contexto del vecindario"
                >{{ old('descripcion', optional($inmueble)->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="inmuebles24_url" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Enlace de Inmuebles24</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        type="url"
                        id="inmuebles24_url"
                        name="inmuebles24_url"
                        value="{{ old('inmuebles24_url', optional($inmueble)->inmuebles24_url) }}"
                        placeholder="https://www.inmuebles24.com/propiedades/..."
                        class="{{ $formControlClasses }} flex-1"
                    >
                    <button
                        type="button"
                        id="extract-inmuebles24-id"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-md shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-300 focus-visible:ring-offset-2 dark:shadow-blue-900/30"
                    >
                        Extraer ID
                    </button>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pega el enlace completo y extraeremos automáticamente el ID numérico antes de .html.</p>
                <p class="text-xs text-blue-600 hidden dark:text-blue-400" data-i24-feedback></p>
            </div>

            <div class="space-y-2">
                <label for="tags" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Tags</label>
                <input
                    type="text"
                    id="tags"
                    name="tags"
                    value="{{ $tagsText }}"
                    placeholder="Ej. Familiar, Pet friendly, Céntrico"
                    class="{{ $formControlClasses }}"
                >
                <p class="text-xs text-slate-500 dark:text-slate-400">Separa cada etiqueta con una coma para organizarlas fácilmente.</p>
                @error('tags')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="space-y-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Características</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Detalle los elementos que ayudan a tomar decisiones rápidas.</p>
                </div>
                <label for="destacado" class="flex items-center justify-between gap-4 text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
    <span class="whitespace-nowrap">Destacar inmueble en listados</span>
    
    <input type="hidden" name="destacado" value="0">
    <input
        type="checkbox"
        id="destacado"
        name="destacado"
        value="1"
        @if ($inmueble)
            data-update-url="{{ route('inmuebles.destacado', $inmueble) }}"
        @endif
        @checked(old('destacado', optional($inmueble)->destacado) == 1)
        class="peer sr-only"
    />
    <div class="w-11 h-6 bg-slate-300 rounded-full peer-checked:bg-green-500 relative transition dark:bg-slate-600 peer-checked:[&>.toggle-knob]:translate-x-5">
        <!-- Bolita -->
        <div class="toggle-knob w-5 h-5 bg-white rounded-full absolute left-0.5 top-0.5 transform transition"></div>
    </div>
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
                    <div class="space-y-3">
                        <label for="{{ $field['id'] }}" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ $field['label'] }}</label>
                        <input
                            type="number"
                            @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
                            min="0"
                            id="{{ $field['id'] }}"
                            name="{{ $field['id'] }}"
                            value="{{ old($field['id'], optional($inmueble)->{$field['id']}) }}"
                            class="{{ $formControlClasses }}"
                        >
                        @error($field['id'])
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="space-y-3">
                    <label for="video_url" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Video del inmueble</label>
                    <input
                        type="url"
                        id="video_url"
                        name="video_url"
                        value="{{ old('video_url', optional($inmueble)->video_url) }}"
                        placeholder="https://www.youtube.com/..."
                        class="{{ $formControlClasses }}"
                    >
                    @error('video_url')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-3">
                    <label for="tour_virtual_url" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Tour virtual</label>
                    <input
                        type="url"
                        id="tour_virtual_url"
                        name="tour_virtual_url"
                        value="{{ old('tour_virtual_url', optional($inmueble)->tour_virtual_url) }}"
                        placeholder="https://my.matterport.com/..."
                        class="{{ $formControlClasses }}"
                    >
                    @error('tour_virtual_url')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-3">
                <label for="amenidades" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Amenidades destacadas</label>
                <textarea
                    id="amenidades"
                    name="amenidades"
                    rows="6"
                    placeholder="Escribe cada amenidad en una línea. Ej. Alberca\nRoof garden\nSeguridad 24/7"
                    class="{{ $textareaControlClasses }}"
                >{{ $amenidadesText }}</textarea>
                @error('amenidades')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-3">
                <label for="extras" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Extras o notas internas</label>
                <textarea
                    id="extras"
                    name="extras"
                    rows="6"
                    placeholder="Ideal para registrar detalles logísticos o recordatorios para el equipo"
                    class="{{ $textareaControlClasses }}"
                >{{ $extrasText }}</textarea>
                @error('extras')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Galería</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Sube hasta 10 fotografías en formato JPG o PNG. Las primeras se mostrarán como portada.</p>
            </div>

            <div class="space-y-3">
                <livewire:property-gallery-manager :inmueble="$inmueble" :watermark-preview-url="$watermarkPreviewUrl ?? ''" />
            </div>
        </div>
    </section>
</div>
