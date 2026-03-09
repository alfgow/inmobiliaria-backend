<x-layouts.admin title="Nuevo inmueble">
    @php
        $oldAmenities = collect(preg_split('/\r\n|\r|\n/', (string) old('amenidades', '')))
            ->map(static fn(string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    @endphp

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #1e293b; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
        html { scroll-behavior: smooth; }

        .premium-shell {
            position: relative;
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.75));
            box-shadow: 0 25px 80px rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(10px);
        }

        .premium-shell::before,
        .premium-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            filter: blur(45px);
            opacity: 0.28;
            pointer-events: none;
        }

        .premium-shell::before {
            right: -120px;
            top: -130px;
            height: 240px;
            width: 240px;
            background: #38bdf8;
        }

        .premium-shell::after {
            bottom: -120px;
            left: -90px;
            height: 220px;
            width: 220px;
            background: #8b5cf6;
        }

        .premium-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(71, 85, 105, 0.45);
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.82), rgba(15, 23, 42, 0.65));
            padding: 1.4rem;
        }
        
        .amenity-toggle {
            transition: all 0.2s ease;
        }
        .amenity-active {
            background-color: rgba(16, 185, 129, 0.1) !important;
            border-color: #10b981 !important;
            color: #34d399 !important;
        }
    </style>

    <div class="mx-auto w-full max-w-6xl px-4 py-10 text-slate-200 custom-scrollbar" x-data="propertyForm()">
        <div class="premium-shell overflow-hidden p-6 sm:p-8 md:p-10">
        <header class="relative z-10 mb-12 flex flex-col justify-between gap-6 border-b border-slate-700/70 pb-8 md:flex-row md:items-end">
            <div>
                <span class="inline-flex rounded-full border border-cyan-400/40 bg-cyan-400/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-300">NUEVO REGISTRO</span>
                <h1 class="mt-3 text-4xl font-bold tracking-tight text-white">Registrar Propiedad</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-300">Configuración premium para publicar tu inmueble con detalles, amenidades y galería profesional.</p>
            </div>
            <div class="flex gap-3">
                <a
                    href="{{ route('inmuebles.index') }}"
                    class="rounded-lg border border-slate-600/80 px-5 py-2 text-sm font-semibold text-slate-200 transition hover:bg-slate-700/40"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    form="main-form"
                    class="rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-cyan-900/40 transition hover:brightness-110"
                >
                    Guardar Cambios
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

            <section id="ubicacion" class="premium-card space-y-6">
                <div class="mb-4 flex items-center space-x-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1e3a8a] text-blue-400 text-sm">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white">1. Informacion de Ubicacion</h2>
                </div>

                <div class="pt-2">
                    <div class="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Titulo del anuncio *</label>
                            <input
                                type="text"
                                name="titulo"
                                value="{{ old('titulo') }}"
                                placeholder="Ej. Departamento de lujo con vista al mar"
                                class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-sm text-slate-200 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                required
                            >
                            @error('titulo')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Precio ($) *</label>
                            <input
                                type="number"
                                name="precio"
                                value="{{ old('precio') }}"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-sm text-slate-200 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                required
                            >
                            @error('precio')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Tipo de operacion *</label>
                            <select
                                name="operacion"
                                class="w-full cursor-pointer appearance-none rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-sm text-slate-200 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                required
                            >
                                <option value="">Selecciona una opcion</option>
                                @foreach ($operaciones as $operacion)
                                    <option value="{{ $operacion }}" @selected(old('operacion') === $operacion)>{{ $operacion }}</option>
                                @endforeach
                            </select>
                            @error('operacion')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Tipo de inmueble *</label>
                            <select
                                name="tipo"
                                class="w-full cursor-pointer appearance-none rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-sm text-slate-200 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                required
                            >
                                <option value="">Selecciona una opcion</option>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ $tipo }}</option>
                                @endforeach
                            </select>
                            @error('tipo')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Codigo postal</label>
                            <input
                                type="text"
                                name="codigo_postal"
                                value="{{ old('codigo_postal') }}"
                                class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-sm text-slate-200 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                            @error('codigo_postal')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Direccion completa *</label>
                            <input
                                type="text"
                                name="direccion"
                                value="{{ old('direccion') }}"
                                placeholder="Calle, numero, colonia, ciudad..."
                                class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-sm text-slate-200 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                required
                            >
                            @error('direccion')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section id="detalles" class="premium-card space-y-6">
                <div class="mb-4 flex items-center space-x-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#064e3b] text-emerald-400 text-sm">
                        <i class="fas fa-home"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white">2. Detalles y Amenidades</h2>
                </div>

                <div class="pt-2">
                    <div class="grid grid-cols-2 gap-x-8 gap-y-6 border-b border-[#2a3649] pb-10">
                        <div class="space-y-2">
                            <label class="block text-center text-[10px] font-bold uppercase tracking-widest text-[#94a3b8]">Habitaciones</label>
                            <input type="number" name="habitaciones" min="0" value="{{ old('habitaciones', 0) }}" class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-center text-sm font-semibold text-white outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-center text-[10px] font-bold uppercase tracking-widest text-[#94a3b8]">Banos</label>
                            <input type="number" name="banos" min="0" value="{{ old('banos', 0) }}" class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-center text-sm font-semibold text-white outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-center text-[10px] font-bold uppercase tracking-widest text-[#94a3b8]">Estacionamientos</label>
                            <input type="number" name="estacionamientos" min="0" value="{{ old('estacionamientos', 0) }}" class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-center text-sm font-semibold text-white outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-center text-[10px] font-bold uppercase tracking-widest text-[#94a3b8]">M2 totales</label>
                            <input type="number" name="metros_cuadrados" min="0" step="0.01" value="{{ old('metros_cuadrados', 0) }}" class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-center text-sm font-semibold text-white outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="space-y-8 pt-8 border-b border-[#2a3649] pb-10">
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-[10px] font-bold text-[#94a3b8] uppercase tracking-widest mb-3">Mas Ambientes</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="item in masAmbientesCatalog" :key="item">
                                        <button type="button" 
                                                @click="toggleAmenity(item)"
                                                :class="isAmenitySelected(item) ? 'amenity-active border-emerald-500 bg-emerald-500/10' : 'text-slate-300 border-[#2a3649] bg-transparent hover:border-emerald-500'"
                                                class="px-4 py-3 rounded-lg border text-xs transition-all flex items-center justify-between text-left">
                                            <span x-text="item"></span>
                                            <i class="fas fa-check-circle ml-2" x-show="isAmenitySelected(item)"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-[10px] font-bold text-[#94a3b8] uppercase tracking-widest mb-3">Servicios</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="item in serviciosCatalog" :key="item">
                                        <button type="button" 
                                                @click="toggleAmenity(item)"
                                                :class="isAmenitySelected(item) ? 'amenity-active border-emerald-500 bg-emerald-500/10' : 'text-slate-300 border-[#2a3649] bg-transparent hover:border-emerald-500'"
                                                class="px-4 py-3 rounded-lg border text-xs transition-all flex items-center justify-between text-left">
                                            <span x-text="item"></span>
                                            <i class="fas fa-check-circle ml-2" x-show="isAmenitySelected(item)"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <textarea name="amenidades" x-model="amenidadesPayload" class="hidden"></textarea>
                            @error('amenidades')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Descripcion narrativa</label>
                            <textarea
                                name="descripcion"
                                rows="4"
                                placeholder="Cuentale a tus prospectos que hace especial a esta propiedad..."
                                class="w-full resize-none rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-sm text-slate-200 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            >{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2 pt-8">
                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Enlace de Inmuebles24 o Vivanuncios</label>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <input
                                    type="url"
                                    id="inmuebles24_url"
                                    name="inmuebles24_url"
                                    value="{{ old('inmuebles24_url') }}"
                                    placeholder="https://www.inmuebles24.com/... o https://www.vivanuncios.com.mx/..."
                                    class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-sm text-slate-200 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                >
                                <button type="button" id="extract-inmuebles24-id" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                    Extraer ID
                                </button>
                            </div>
                            <p class="mt-2 text-[10px] text-slate-400">Pega el enlace completo y extraeremos el ID numerico a tags.</p>
                            <p class="hidden text-xs text-blue-400" data-i24-feedback></p>
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Tags</label>
                            <input
                                type="text"
                                id="tags"
                                name="tags"
                                value="{{ old('tags') }}"
                                placeholder="Ej. Familiar, Pet friendly, Centrico"
                                class="w-full rounded-lg border border-[#2a3649] bg-transparent px-4 py-3 text-sm text-slate-200 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                            @error('tags')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section id="multimedia" class="premium-card space-y-6">
                <div class="mb-4 flex items-center space-x-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#4c1d95] text-purple-400 text-sm">
                        <i class="fas fa-images"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white">3. Galería de Fotos</h2>
                </div>

                <div class="pt-2">
                    <div class="space-y-8">
                        <div class="w-full">
                            <label class="group flex h-40 w-full cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-[#475569] bg-transparent text-slate-400 transition hover:border-purple-500 hover:text-purple-400">
                                <input
                                    type="file"
                                    name="imagenes[]"
                                    x-ref="fileInput"
                                    @change="handleUpload"
                                    class="hidden"
                                    accept="image/*"
                                    multiple
                                >
                                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-[#1e293b] text-slate-300 transition group-hover:bg-purple-600/20 group-hover:text-purple-300">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-widest">Haz clic o arrastra fotos aqui</span>
                                <span class="mt-1 text-[10px] text-slate-500" x-text="photos.length + ' de 15 seleccionadas'"></span>
                            </label>
                            @error('imagenes')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                            @error('imagenes.*')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5" x-show="photos.length > 0" x-transition>
                            <template x-for="(image, index) in photos" :key="index">
                                <div class="group relative aspect-square overflow-hidden rounded-xl border border-[#334155] bg-transparent">
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

            <footer class="pb-10 pt-6">
                <button type="submit" class="group flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-blue-500 via-cyan-500 to-violet-600 px-10 py-4 font-bold text-white shadow-xl shadow-blue-900/40 transition hover:scale-[1.01] hover:brightness-110">
                    <i class="fas fa-rocket mr-3 group-hover:animate-bounce"></i> PUBLICAR PROPIEDAD
                </button>
            </footer>
        </form>
    </div>
    </div>

    <script>
        function propertyForm() {
            return {
                photos: [],
                selectedAmenities: @js($oldAmenities),
                amenidadesPayload: @js(old('amenidades', '')),
                masAmbientesCatalog: [
                    'Cocina integral',
                    'Cuarto de juegos',
                    'Cuarto de servicio',
                    'Cuarto de TV',
                    'Estudio',
                    'Sotano',
                    'Oficina',
                ],
                serviciosCatalog: [
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

                    if (files.length > 15) {
                        window.alert('Limite de 15 fotos.');
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
