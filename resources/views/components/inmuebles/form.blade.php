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

    // Modern form control classes with better focus states
    $formControlClasses = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-700 placeholder-slate-400 shadow-sm transition-all duration-200 hover:border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:placeholder-slate-500 dark:hover:border-slate-500 dark:focus:border-blue-500 dark:focus:ring-blue-500/20';
    $selectControlClasses = $formControlClasses . ' pr-10 appearance-none';
    $textareaControlClasses = $formControlClasses . ' min-h-[6rem] resize-y';

    // Section header style
    $sectionHeaderClass = 'flex items-center gap-3 text-lg font-semibold text-slate-900 dark:text-slate-100';
    $sectionDescClass = 'mt-1 text-sm text-slate-500 dark:text-slate-400';
    $labelClass = 'mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
@endphp

<div class="space-y-6">
    {{-- Section: Información General --}}
    <section class="group rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="{{ $sectionHeaderClass }}">Información general</h2>
                    <p class="{{ $sectionDescClass }}">Datos principales del inmueble que se mostrarán en la ficha pública.</p>
                </div>
            </div>
        </div>

        <div class="space-y-6 p-6">
            {{-- Título y Precio --}}
            <div class="grid gap-6 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <label for="titulo" class="{{ $labelClass }}">
                        Título <span class="text-red-500">*</span>
                    </label>
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
                        <p class="mt-2 flex items-center gap-1.5 text-sm text-red-500">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="lg:col-span-4">
                    <label for="precio" class="{{ $labelClass }}">
                        Precio <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                            <span class="text-lg font-semibold">$</span>
                        </span>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="precio"
                            name="precio"
                            value="{{ old('precio', optional($inmueble)->precio) }}"
                            class="{{ $formControlClasses }} pl-10 font-mono text-lg"
                            required
                        >
                    </div>
                    @error('precio')
                        <p class="mt-2 flex items-center gap-1.5 text-sm text-red-500">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Dirección --}}
            <div>
                <label for="direccion" class="{{ $labelClass }}">
                    Dirección <span class="text-red-500">*</span>
                </label>
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
                    <p class="mt-2 flex items-center gap-1.5 text-sm text-red-500">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Map --}}
            <div>
                <label class="{{ $labelClass }}">Ubicación en mapa</label>
                <div
                    id="inmueble-map"
                    data-postal-resolve-url="{{ route('codigos-postales.resolve') }}"
                    class="h-64 w-full overflow-hidden rounded-xl border border-slate-200 shadow-inner transition-shadow hover:shadow-md dark:border-slate-600"
                ></div>
                <input type="hidden" name="latitud" value="{{ old('latitud', optional($inmueble)->latitud) }}">
                <input type="hidden" name="longitud" value="{{ old('longitud', optional($inmueble)->longitud) }}">
                @error('latitud')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- CP + Colonia + Municipio + Estado --}}
            <section
                class="space-y-6"
                data-postal-selector
                data-postal-options-url="{{ route('codigos-postales.index') }}"
            >
                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label for="codigo_postal" class="{{ $labelClass }}">Código Postal</label>
                        <div data-searchable-select data-search-placeholder="Buscar C.P.">
                            <select id="codigo_postal" name="codigo_postal" class="{{ $selectControlClasses }}">
                                <option value="">Selecciona una opción</option>
                                @if ($selectedCodigoPostal)
                                    <option value="{{ $selectedCodigoPostal }}" selected>{{ $selectedCodigoPostal }}</option>
                                @endif
                            </select>
                        </div>
                        @error('codigo_postal')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="colonia" class="{{ $labelClass }}">Colonia</label>
                        <div data-searchable-select data-search-placeholder="Buscar colonia">
                            <select id="colonia" name="colonia" class="{{ $selectControlClasses }}">
                                <option value="">Selecciona una opción</option>
                                @if ($selectedColonia)
                                    <option value="{{ $selectedColonia }}" selected>{{ $selectedColonia }}</option>
                                @endif
                            </select>
                        </div>
                        @error('colonia')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="municipio" class="{{ $labelClass }}">Municipio</label>
                        <div data-searchable-select data-search-placeholder="Buscar municipio">
                            <select id="municipio" name="municipio" class="{{ $selectControlClasses }}">
                                <option value="">Selecciona una opción</option>
                                @if ($selectedMunicipio)
                                    <option value="{{ $selectedMunicipio }}" selected>{{ $selectedMunicipio }}</option>
                                @endif
                            </select>
                        </div>
                        @error('municipio')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="estado" class="{{ $labelClass }}">Estado</label>
                        <div data-searchable-select data-search-placeholder="Buscar estado">
                            <select id="estado" name="estado" class="{{ $selectControlClasses }}">
                                <option value="">Selecciona una opción</option>
                                @if ($selectedEstado)
                                    <option value="{{ $selectedEstado }}" selected>{{ $selectedEstado }}</option>
                                @endif
                            </select>
                        </div>
                        @error('estado')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Tipo, Operación, Estatus --}}
            <div class="grid gap-6 {{ $showStatusSelector ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
                <div>
                    <label for="tipo" class="{{ $labelClass }}">
                        Tipo <span class="text-red-500">*</span>
                    </label>
                    <select id="tipo" name="tipo" class="{{ $selectControlClasses }}" required>
                        <option value="">Selecciona una opción</option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo', optional($inmueble)->tipo) === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                    @error('tipo')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="operacion" class="{{ $labelClass }}">
                        Operación <span class="text-red-500">*</span>
                    </label>
                    <select id="operacion" name="operacion" class="{{ $selectControlClasses }}" required>
                        <option value="">Selecciona una opción</option>
                        @foreach ($operaciones as $operacion)
                            <option value="{{ $operacion }}" @selected(old('operacion', optional($inmueble)->operacion) === $operacion)>{{ $operacion }}</option>
                        @endforeach
                    </select>
                    @error('operacion')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                @if ($showStatusSelector)
                    <div>
                        <label for="estatus_id" class="{{ $labelClass }}">
                            Estatus <span class="text-red-500">*</span>
                        </label>
                        <select id="estatus_id" name="estatus_id" class="estatus-select {{ $selectControlClasses }}" required>
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
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <input type="hidden" id="commission_percentage" name="commission_percentage" value="{{ old('commission_percentage', optional($inmueble)->commission_percentage) }}">
                <input type="hidden" id="commission_amount" name="commission_amount" value="{{ old('commission_amount', optional($inmueble)->commission_amount) }}">
                <input type="hidden" id="commission_status_id" name="commission_status_id" value="{{ old('commission_status_id', optional($inmueble)->commission_status_id) }}">
                <input type="hidden" id="commission_status_name" name="commission_status_name" value="{{ old('commission_status_name', optional($inmueble)->commission_status_name) }}">
            </div>

            {{-- Descripción --}}
            <div>
                <label for="descripcion" class="{{ $labelClass }}">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="5"
                    class="{{ $textareaControlClasses }}"
                    placeholder="Cuenta la historia del inmueble, puntos fuertes y contexto del vecindario"
                >{{ old('descripcion', optional($inmueble)->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Inmuebles24 URL --}}
            <div>
                <label for="inmuebles24_url" class="{{ $labelClass }}">Enlace de Inmuebles24</label>
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
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-slate-900 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:bg-slate-700 dark:hover:bg-slate-600"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Extraer ID
                    </button>
                </div>
                <p class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pega el enlace completo y extraeremos automáticamente el ID numérico.
                </p>
                <p class="mt-1 hidden text-xs text-blue-600 dark:text-blue-400" data-i24-feedback></p>
            </div>

            {{-- Tags --}}
            <div>
                <label for="tags" class="{{ $labelClass }}">Tags</label>
                <input
                    type="text"
                    id="tags"
                    name="tags"
                    value="{{ $tagsText }}"
                    placeholder="Ej. Familiar, Pet friendly, Céntrico"
                    class="{{ $formControlClasses }}"
                >
                <p class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Separa cada etiqueta con una coma para organizarlas fácilmente.
                </p>
                @error('tags')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    {{-- Section: Características --}}
    <section class="group rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-md">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="{{ $sectionHeaderClass }}">Características</h2>
                        <p class="{{ $sectionDescClass }}">Detalles que ayudan a tomar decisiones rápidas.</p>
                    </div>
                </div>

                {{-- Toggle Destacado --}}
                <label for="destacado" class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:border-violet-300 hover:shadow-md dark:border-slate-600 dark:bg-slate-700">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Destacar en listados</span>
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
                    <div class="relative h-6 w-11 rounded-full bg-slate-300 transition-colors peer-checked:bg-violet-500 dark:bg-slate-600">
                        <div class="toggle-knob absolute left-0.5 top-0.5 h-5 w-5 transform rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></div>
                    </div>
                </label>
            </div>
        </div>

        <div class="p-6">
            {{-- Feature Fields Grid --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $featureFields = [
                        ['id' => 'habitaciones', 'label' => 'Habitaciones', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['id' => 'banos', 'label' => 'Baños', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                        ['id' => 'estacionamientos', 'label' => 'Estacionamientos', 'icon' => 'M5 10l7-7m0 0l7 7m-7-7v18'],
                        ['id' => 'metros_cuadrados', 'label' => 'Metros cuadrados (m²)', 'step' => '0.01'],
                        ['id' => 'superficie_construida', 'label' => 'Superficie construida (m²)', 'step' => '0.01'],
                        ['id' => 'superficie_terreno', 'label' => 'Superficie terreno (m²)', 'step' => '0.01'],
                        ['id' => 'anio_construccion', 'label' => 'Año de construcción'],
                    ];
                @endphp

                @foreach ($featureFields as $field)
                    <div class="relative">
                        <label for="{{ $field['id'] }}" class="{{ $labelClass }}">{{ $field['label'] }}</label>
                        <div class="relative">
                            @if (isset($field['icon']))
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $field['icon'] }}" />
                                    </svg>
                                </span>
                            @endif
                            <input
                                type="number"
                                @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
                                min="0"
                                id="{{ $field['id'] }}"
                                name="{{ $field['id'] }}"
                                value="{{ old($field['id'], optional($inmueble)->{$field['id']}) }}"
                                class="{{ $formControlClasses }} {{ isset($field['icon']) ? 'pl-10' : '' }}"
                            >
                        </div>
                        @error($field['id'])
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            {{-- Video URLs --}}
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div>
                    <label for="video_url" class="{{ $labelClass }}">Video del inmueble</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <input
                            type="url"
                            id="video_url"
                            name="video_url"
                            value="{{ old('video_url', optional($inmueble)->video_url) }}"
                            placeholder="https://www.youtube.com/..."
                            class="{{ $formControlClasses }} pl-10"
                        >
                    </div>
                    @error('video_url')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tour_virtual_url" class="{{ $labelClass }}">Tour virtual</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <input
                            type="url"
                            id="tour_virtual_url"
                            name="tour_virtual_url"
                            value="{{ old('tour_virtual_url', optional($inmueble)->tour_virtual_url) }}"
                            placeholder="https://my.matterport.com/..."
                            class="{{ $formControlClasses }} pl-10"
                        >
                    </div>
                    @error('tour_virtual_url')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </section>

    {{-- Section: Amenidades y Extras --}}
    <section class="group rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <div>
                    <h2 class="{{ $sectionHeaderClass }}">Amenidades y Extras</h2>
                    <p class="{{ $sectionDescClass }}">Características adicionales y notas internas.</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 p-6 lg:grid-cols-2">
            <div>
                <label for="amenidades" class="{{ $labelClass }}">Amenidades destacadas</label>
                <textarea
                    id="amenidades"
                    name="amenidades"
                    rows="6"
                    placeholder="Escribe cada amenidad en una línea. Ej. Alberca&#10;Roof garden&#10;Seguridad 24/7"
                    class="{{ $textareaControlClasses }}"
                >{{ $amenidadesText }}</textarea>
                @error('amenidades')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="extras" class="{{ $labelClass }}">Extras o notas internas</label>
                <textarea
                    id="extras"
                    name="extras"
                    rows="6"
                    placeholder="Detalles logísticos o recordatorios para el equipo"
                    class="{{ $textareaControlClasses }}"
                >{{ $extrasText }}</textarea>
                @error('extras')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    {{-- Section: Galería --}}
    <section class="group rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="{{ $sectionHeaderClass }}">Galería de fotos</h2>
                    <p class="{{ $sectionDescClass }}">Sube hasta 10 fotografías en formato JPG o PNG. La primera será la portada.</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <livewire:property-gallery-manager :inmueble="$inmueble" :watermark-preview-url="$watermarkPreviewUrl ?? ''" />
        </div>
    </section>
</div>
