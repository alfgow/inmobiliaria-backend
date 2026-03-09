<x-layouts.admin title="Nuevo inmueble">
    @php
        $oldAmenities = collect(preg_split('/\r\n|\r|\n/', (string) old('amenidades', '')))
            ->map(static fn(string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    @endphp

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="mx-auto w-full max-w-5xl px-4 py-10 text-slate-200" x-data="propertyForm()">
        <header class="mb-12 flex flex-col justify-between gap-4 border-b border-slate-800 pb-8 md:flex-row md:items-end">
            <div>
                <span class="text-xs font-semibold uppercase tracking-widest text-blue-500">Nuevo registro</span>
                <h1 class="mt-2 text-4xl font-bold text-white">Registrar propiedad</h1>
                <p class="mt-2 text-lg text-slate-400">Configuracion completa del inmueble y carga multimedia.</p>
            </div>
            <div class="flex gap-3">
                <a
                    href="{{ route('inmuebles.index') }}"
                    class="rounded-xl border border-slate-700 px-6 py-2.5 font-bold text-slate-300 transition hover:bg-slate-800"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    form="main-form"
                    class="rounded-xl bg-blue-600 px-8 py-2.5 font-bold text-white shadow-lg shadow-blue-900/20 transition hover:bg-blue-700"
                >
                    Guardar cambios
                </button>
            </div>
        </header>

        @if ($errors->any())
            <div class="mb-8 rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
                Corrige los campos marcados para continuar.
            </div>
        @endif

        <form
            id="main-form"
            action="{{ route('inmuebles.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-12"
            data-swal-loader="registrar-inmueble"
            data-swal-loader-title="Registrando inmueble"
            data-swal-loader-text="Estamos guardando la informacion del inmueble..."
        >
            @csrf

            <input type="hidden" name="latitud" value="{{ old('latitud') }}">
            <input type="hidden" name="longitud" value="{{ old('longitud') }}">

            <section id="ubicacion" class="space-y-6">
                <div class="mb-4 flex items-center space-x-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border border-blue-500/30 bg-blue-600/20">
                        <i class="fas fa-map-marker-alt text-blue-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">1. Informacion de ubicacion</h2>
                </div>

                <div class="rounded-3xl border border-slate-800 bg-[#1e293b] p-8 shadow-xl">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Titulo del anuncio *</label>
                            <input
                                type="text"
                                name="titulo"
                                value="{{ old('titulo') }}"
                                placeholder="Ej. Departamento de lujo con vista al mar"
                                class="w-full rounded-xl border border-slate-700 bg-[#0f172a] px-5 py-4 text-lg text-slate-200 outline-none transition focus:ring-2 focus:ring-blue-600"
                                required
                            >
                            @error('titulo')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Precio ($) *</label>
                            <input
                                type="number"
                                name="precio"
                                value="{{ old('precio') }}"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-slate-700 bg-[#0f172a] px-5 py-4 text-lg text-slate-200 outline-none transition focus:ring-2 focus:ring-blue-600"
                                required
                            >
                            @error('precio')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Tipo de operacion *</label>
                            <select
                                name="operacion"
                                class="w-full cursor-pointer appearance-none rounded-xl border border-slate-700 bg-[#0f172a] px-5 py-4 text-slate-200 outline-none transition focus:ring-2 focus:ring-blue-600"
                                required
                            >
                                <option value="">Selecciona una opcion</option>
                                @foreach ($operaciones as $operacion)
                                    <option value="{{ $operacion }}" @selected(old('operacion') === $operacion)>{{ $operacion }}</option>
                                @endforeach
                            </select>
                            @error('operacion')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Tipo de inmueble *</label>
                            <select
                                name="tipo"
                                class="w-full cursor-pointer appearance-none rounded-xl border border-slate-700 bg-[#0f172a] px-5 py-4 text-slate-200 outline-none transition focus:ring-2 focus:ring-blue-600"
                                required
                            >
                                <option value="">Selecciona una opcion</option>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ $tipo }}</option>
                                @endforeach
                            </select>
                            @error('tipo')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Codigo postal</label>
                            <input
                                type="text"
                                name="codigo_postal"
                                value="{{ old('codigo_postal') }}"
                                class="w-full rounded-xl border border-slate-700 bg-[#0f172a] px-5 py-4 text-slate-200 outline-none transition focus:ring-2 focus:ring-blue-600"
                            >
                            @error('codigo_postal')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Direccion completa *</label>
                            <input
                                type="text"
                                name="direccion"
                                value="{{ old('direccion') }}"
                                placeholder="Calle, numero, colonia, ciudad..."
                                class="w-full rounded-xl border border-slate-700 bg-[#0f172a] px-5 py-4 text-slate-200 outline-none transition focus:ring-2 focus:ring-blue-600"
                                required
                            >
                            @error('direccion')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section id="detalles" class="space-y-6">
                <div class="mb-4 flex items-center space-x-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-600/20">
                        <i class="fas fa-house-chimney text-emerald-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">2. Detalles y amenidades</h2>
                </div>

                <div class="space-y-10 rounded-3xl border border-slate-800 bg-[#1e293b] p-8 shadow-xl">
                    <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                        <div class="space-y-2">
                            <label class="block text-center text-[10px] font-bold uppercase tracking-widest text-slate-500">Habitaciones</label>
                            <input type="number" name="habitaciones" min="0" value="{{ old('habitaciones', 0) }}" class="w-full rounded-2xl border border-slate-700 bg-[#0f172a] px-4 py-5 text-center text-xl font-bold text-slate-200 outline-none transition focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-center text-[10px] font-bold uppercase tracking-widest text-slate-500">Banos</label>
                            <input type="number" name="banos" min="0" value="{{ old('banos', 0) }}" class="w-full rounded-2xl border border-slate-700 bg-[#0f172a] px-4 py-5 text-center text-xl font-bold text-slate-200 outline-none transition focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-center text-[10px] font-bold uppercase tracking-widest text-slate-500">Estacionamientos</label>
                            <input type="number" name="estacionamientos" min="0" value="{{ old('estacionamientos', 0) }}" class="w-full rounded-2xl border border-slate-700 bg-[#0f172a] px-4 py-5 text-center text-xl font-bold text-slate-200 outline-none transition focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-center text-[10px] font-bold uppercase tracking-widest text-slate-500">M2 totales</label>
                            <input type="number" name="metros_cuadrados" min="0" step="0.01" value="{{ old('metros_cuadrados', 0) }}" class="w-full rounded-2xl border border-slate-700 bg-[#0f172a] px-4 py-5 text-center text-xl font-bold text-slate-200 outline-none transition focus:ring-2 focus:ring-emerald-600">
                        </div>
                    </div>

                    <div class="space-y-8 border-t border-slate-800 pt-8">
                        <div>
                            <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">Amenidades</h3>
                            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                                <template x-for="item in amenityCatalog" :key="item">
                                    <button
                                        type="button"
                                        @click="toggleAmenity(item)"
                                        :class="isAmenitySelected(item) ? 'border-emerald-500 bg-emerald-500/10 text-emerald-300' : 'border-slate-700 bg-[#0f172a] text-slate-200'"
                                        class="flex items-center justify-between rounded-xl border px-4 py-3 text-left text-xs font-semibold transition-all hover:border-emerald-500"
                                    >
                                        <span x-text="item"></span>
                                        <i class="fas fa-check-circle ml-2" x-show="isAmenitySelected(item)"></i>
                                    </button>
                                </template>
                            </div>
                            <textarea name="amenidades" x-model="amenidadesPayload" class="hidden"></textarea>
                            @error('amenidades')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Descripcion narrativa</label>
                            <textarea
                                name="descripcion"
                                rows="4"
                                placeholder="Cuentale a tus prospectos que hace especial a esta propiedad..."
                                class="w-full resize-none rounded-2xl border border-slate-700 bg-[#0f172a] px-6 py-5 leading-relaxed text-slate-200 outline-none transition focus:ring-2 focus:ring-emerald-600"
                            >{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Enlace de Inmuebles24 o Vivanuncios</label>
                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <input
                                        type="url"
                                        id="inmuebles24_url"
                                        name="inmuebles24_url"
                                        value="{{ old('inmuebles24_url') }}"
                                        placeholder="https://www.inmuebles24.com/... o https://www.vivanuncios.com.mx/..."
                                        class="w-full rounded-xl border border-slate-700 bg-[#0f172a] px-5 py-4 text-slate-200 outline-none transition focus:ring-2 focus:ring-blue-600"
                                    >
                                    <button type="button" id="extract-inmuebles24-id" class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                                        Extraer ID
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-slate-400">Pega el enlace completo y extraeremos el ID numerico a tags.</p>
                                <p class="hidden text-xs text-blue-400" data-i24-feedback></p>
                            </div>

                            <div>
                                <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Tags</label>
                                <input
                                    type="text"
                                    id="tags"
                                    name="tags"
                                    value="{{ old('tags') }}"
                                    placeholder="Ej. Familiar, Pet friendly, Centrico"
                                    class="w-full rounded-xl border border-slate-700 bg-[#0f172a] px-5 py-4 text-slate-200 outline-none transition focus:ring-2 focus:ring-blue-600"
                                >
                                @error('tags')
                                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="multimedia" class="space-y-6">
                <div class="mb-4 flex items-center space-x-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border border-purple-500/30 bg-purple-600/20">
                        <i class="fas fa-images text-purple-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">3. Galeria de fotos</h2>
                </div>

                <div class="rounded-3xl border border-slate-800 bg-[#1e293b] p-8 shadow-xl">
                    <div class="space-y-8">
                        <div class="w-full">
                            <label class="group flex h-40 w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-700 text-slate-500 transition hover:border-purple-500 hover:bg-purple-500/5 hover:text-purple-400">
                                <input
                                    type="file"
                                    name="imagenes[]"
                                    x-ref="fileInput"
                                    @change="handleUpload"
                                    class="hidden"
                                    accept="image/*"
                                    multiple
                                >
                                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#0f172a] transition group-hover:bg-purple-600/20">
                                    <i class="fas fa-cloud-arrow-up text-xl"></i>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-widest">Haz clic o arrastra fotos aqui</span>
                                <span class="mt-1 text-[10px] text-slate-500" x-text="photos.length + ' de 10 seleccionadas'"></span>
                            </label>
                            @error('imagenes')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                            @error('imagenes.*')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5" x-show="photos.length > 0" x-transition>
                            <template x-for="(image, index) in photos" :key="index">
                                <div class="group relative aspect-square overflow-hidden rounded-2xl border border-slate-700 bg-[#0f172a]">
                                    <img :src="image.url" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition group-hover:opacity-100">
                                        <button @click.prevent="removePhoto(index)" type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-white transition hover:scale-110">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                    <div x-show="index === 0" class="absolute left-2 top-2 rounded bg-blue-600 px-2 py-0.5 text-[8px] font-bold uppercase shadow">Portada</div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="pb-20 pt-10">
                <button type="submit" class="group flex w-full items-center justify-center rounded-2xl bg-blue-600 px-12 py-5 text-xl font-extrabold text-white shadow-2xl transition hover:bg-blue-700">
                    <i class="fas fa-rocket mr-3 group-hover:animate-bounce"></i> PUBLICAR PROPIEDAD
                </button>
            </footer>
        </form>
    </div>

    <script>
        function propertyForm() {
            return {
                photos: [],
                selectedAmenities: @js($oldAmenities),
                amenidadesPayload: @js(old('amenidades', '')),
                amenityCatalog: [
                    'Cocina integral',
                    'Cuarto de juegos',
                    'Cuarto de servicio',
                    'Cuarto de TV',
                    'Estudio',
                    'Sotano',
                    'Oficina',
                    'Acceso discapacitados',
                    'Aire acondicionado',
                    'Caseta de guardia',
                    'Internet/Wifi',
                    'Seguridad privada',
                    'Servicios basicos',
                    'Calefaccion',
                    'Estacionamiento visitas',
                ],
                init() {
                    if (!this.selectedAmenities.length && this.amenidadesPayload) {
                        this.selectedAmenities = this.amenidadesPayload
                            .split(/\r\n|\r|\n/)
                            .map((item) => item.trim())
                            .filter((item) => item !== '');
                    }

                    this.syncAmenities();
                },
                handleUpload(event) {
                    const files = Array.from(event.target.files || []);

                    if (files.length > 10) {
                        window.alert('Limite de 10 fotos.');
                        event.target.value = '';
                        this.photos = [];
                        return;
                    }

                    this.photos = [];

                    files.forEach((file) => {
                        const reader = new FileReader();
                        reader.onload = (loadEvent) => {
                            this.photos.push({
                                url: loadEvent.target?.result || '',
                                name: file.name,
                            });
                        };
                        reader.readAsDataURL(file);
                    });
                },
                removePhoto(index) {
                    const input = this.$refs.fileInput;

                    if (!input) {
                        return;
                    }

                    const files = Array.from(input.files || []);
                    files.splice(index, 1);

                    const transfer = new DataTransfer();
                    files.forEach((file) => transfer.items.add(file));
                    input.files = transfer.files;

                    this.handleUpload({ target: input });
                },
                toggleAmenity(name) {
                    if (this.selectedAmenities.includes(name)) {
                        this.selectedAmenities = this.selectedAmenities.filter((item) => item !== name);
                    } else {
                        this.selectedAmenities.push(name);
                    }

                    this.syncAmenities();
                },
                isAmenitySelected(name) {
                    return this.selectedAmenities.includes(name);
                },
                syncAmenities() {
                    this.amenidadesPayload = this.selectedAmenities.join('\n');
                },
            };
        }
    </script>
</x-layouts.admin>
