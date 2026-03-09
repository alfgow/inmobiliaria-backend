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
        
        .amenity-toggle {
            transition: all 0.2s ease;
        }
        .amenity-active {
            background-color: rgba(16, 185, 129, 0.1) !important;
            border-color: #10b981 !important;
            color: #34d399 !important;
        }
    </style>

    <div class="max-w-5xl mx-auto px-4 py-10 text-slate-200 font-sans custom-scrollbar" x-data="propertyForm()">
        
        <!-- Header -->
        <header class="mb-12 border-b border-slate-800 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <span class="text-blue-500 font-semibold tracking-widest text-xs uppercase">Nuevo Registro</span>
                <h1 class="text-4xl font-bold mt-2 text-white">Registrar Propiedad</h1>
                <p class="text-slate-400 mt-2 text-lg">Configuración completa del inmueble y carga multimedia.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('inmuebles.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-700 font-bold text-slate-300 hover:bg-slate-800 transition block text-center">
                    Cancelar
                </a>
                <button type="submit" form="main-form" class="px-8 py-2.5 rounded-xl bg-blue-600 font-bold text-white hover:bg-blue-700 shadow-lg shadow-blue-900/20 transition">
                    Guardar Cambios
                </button>
            </div>
        </header>

        @if ($errors->any())
            <div class="mb-8 rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
                Corrige los campos marcados para continuar.
            </div>
        @endif

        <form id="main-form" action="{{ route('inmuebles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-12" data-swal-loader="registrar-inmueble" data-swal-loader-title="Registrando inmueble" data-swal-loader-text="Estamos guardando la informacion del inmueble...">
            @csrf
            
            <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud', '19.4326') }}">
            <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud', '-99.1332') }}">

            <!-- SECCIÓN 1: Ubicación -->
            <section id="ubicacion" class="space-y-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-600/20 flex items-center justify-center border border-blue-500/30">
                        <i class="fas fa-map-marker-alt text-blue-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">1. Información de Ubicación</h2>
                </div>

                <div class="bg-[#1e293b] p-8 rounded-3xl shadow-xl border border-slate-800">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Título del Anuncio *</label>
                            <input type="text" name="titulo" value="{{ old('titulo') }}" placeholder="Ej. Departamento de lujo con vista al mar" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-5 py-4 focus:ring-2 focus:ring-blue-600 outline-none transition text-lg" required>
                            @error('titulo')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Precio ($) *</label>
                            <input type="number" name="precio" value="{{ old('precio') }}" step="0.01" min="0" placeholder="0.00" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-5 py-4 focus:ring-2 focus:ring-blue-600 outline-none transition text-lg" required>
                            @error('precio')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Tipo de Operación *</label>
                            <select name="operacion" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-5 py-4 focus:ring-2 focus:ring-blue-600 outline-none transition appearance-none cursor-pointer" required>
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
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Tipo de Inmueble *</label>
                            <select name="tipo" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-5 py-4 focus:ring-2 focus:ring-blue-600 outline-none transition appearance-none cursor-pointer" required>
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
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Código Postal</label>
                            <input type="text" name="codigo_postal" value="{{ old('codigo_postal') }}" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-5 py-4 focus:ring-2 focus:ring-blue-600 outline-none transition text-lg">
                            @error('codigo_postal')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Dirección Completa *</label>
                            <input type="text" name="direccion" value="{{ old('direccion') }}" placeholder="Calle, número, colonia, ciudad..." class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-5 py-4 focus:ring-2 focus:ring-blue-600 outline-none transition" required>
                            @error('direccion')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Mapa de Ubicación -->
                    <div class="mt-8 rounded-2xl overflow-hidden border border-slate-700 h-80 relative bg-[#0f172a] group">
                        <img src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="w-full h-full object-cover opacity-40 grayscale group-hover:opacity-50 transition-opacity duration-500" alt="Mapa">
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-blue-900/10">
                            <i class="fas fa-location-dot text-blue-500 text-5xl mb-4 drop-shadow-lg animate-bounce"></i>
                            <div class="bg-[#1e293b]/90 backdrop-blur-md px-6 py-3 rounded-2xl shadow-2xl border border-slate-700 text-center">
                                <span class="text-sm font-semibold block">Arrastra el marcador para fijar la posición exacta</span>
                                <span class="text-[10px] text-slate-400 uppercase mt-1 tracking-widest">Lat: 19.4326 / Lng: -99.1332</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 2: Detalles y Amenidades -->
            <section id="detalles" class="space-y-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-600/20 flex items-center justify-center border border-emerald-500/30">
                        <i class="fas fa-house-chimney text-emerald-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">2. Detalles y Amenidades</h2>
                </div>

                <div class="bg-[#1e293b] p-8 rounded-3xl shadow-xl border border-slate-800 space-y-10">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase text-center tracking-widest">Habitaciones</label>
                            <input type="number" name="habitaciones" min="0" value="{{ old('habitaciones', 0) }}" class="text-center w-full bg-[#0f172a] border border-slate-700 rounded-2xl px-4 py-5 focus:ring-2 focus:ring-emerald-600 outline-none transition text-xl font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase text-center tracking-widest">Baños</label>
                            <input type="number" name="banos" min="0" value="{{ old('banos', 0) }}" class="text-center w-full bg-[#0f172a] border border-slate-700 rounded-2xl px-4 py-5 focus:ring-2 focus:ring-emerald-600 outline-none transition text-xl font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase text-center tracking-widest">Estacionamientos</label>
                            <input type="number" name="estacionamientos" min="0" value="{{ old('estacionamientos', 0) }}" class="text-center w-full bg-[#0f172a] border border-slate-700 rounded-2xl px-4 py-5 focus:ring-2 focus:ring-emerald-600 outline-none transition text-xl font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase text-center tracking-widest">M² Totales</label>
                            <input type="number" name="metros_cuadrados" min="0" step="0.01" value="{{ old('metros_cuadrados', 0) }}" class="text-center w-full bg-[#0f172a] border border-slate-700 rounded-2xl px-4 py-5 focus:ring-2 focus:ring-emerald-600 outline-none transition text-xl font-bold">
                        </div>
                    </div>

                    <div class="space-y-8 border-t border-slate-800 pt-8">
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Más Ambientes</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <template x-for="item in masAmbientesCatalog" :key="item">
                                    <button type="button" 
                                            @click="toggleAmenity(item)"
                                            :class="isAmenitySelected(item) ? 'amenity-active' : ''"
                                            class="amenity-toggle px-4 py-3 rounded-xl border border-slate-700 bg-[#0f172a] text-xs font-semibold hover:border-emerald-500 transition-all flex items-center justify-between text-left">
                                        <span x-text="item"></span>
                                        <i class="fas fa-check-circle ml-2" x-show="isAmenitySelected(item)"></i>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Servicios</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <template x-for="item in serviciosCatalog" :key="item">
                                    <button type="button" 
                                            @click="toggleAmenity(item)"
                                            :class="isAmenitySelected(item) ? 'amenity-active' : ''"
                                            class="amenity-toggle px-4 py-3 rounded-xl border border-slate-700 bg-[#0f172a] text-xs font-semibold hover:border-emerald-500 transition-all flex items-center justify-between text-left">
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

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Descripción Narrativa</label>
                            <textarea name="descripcion" rows="4" placeholder="Cuéntale a tus prospectos qué hace especial a esta propiedad..." class="w-full bg-[#0f172a] border border-slate-700 rounded-2xl px-6 py-5 focus:ring-2 focus:ring-emerald-600 outline-none transition resize-none leading-relaxed">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 pt-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Enlace de Inmuebles24 o Vivanuncios</label>
                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <input
                                        type="url"
                                        id="inmuebles24_url"
                                        name="inmuebles24_url"
                                        value="{{ old('inmuebles24_url') }}"
                                        placeholder="https://www.inmuebles24.com/..."
                                        class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-5 py-4 focus:ring-2 focus:ring-blue-600 outline-none transition"
                                    >
                                    <button type="button" id="extract-inmuebles24-id" class="rounded-xl border border-slate-700 bg-blue-600 px-6 py-4 text-sm font-semibold text-white transition hover:bg-blue-700 shadow-lg shadow-blue-900/20 truncate">
                                        Extraer ID
                                    </button>
                                </div>
                                <p class="mt-2 text-[10px] text-slate-500">Pega el enlace completo y extraeremos el ID numérico a tags.</p>
                                <p class="hidden text-xs text-blue-400 mt-1" data-i24-feedback></p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Tags</label>
                                <input
                                    type="text"
                                    id="tags"
                                    name="tags"
                                    value="{{ old('tags') }}"
                                    placeholder="Ej. Familiar, Pet friendly, Céntrico"
                                    class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-5 py-4 focus:ring-2 focus:ring-blue-600 outline-none transition"
                                >
                                @error('tags')
                                    <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- SECCIÓN 3: Multimedia (Galería Full Width) -->
            <section id="multimedia" class="space-y-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-10 h-10 rounded-full bg-purple-600/20 flex items-center justify-center border border-purple-500/30">
                        <i class="fas fa-images text-purple-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">3. Galería de Fotos</h2>
                </div>

                <div class="bg-[#1e293b] p-8 rounded-3xl shadow-xl border border-slate-800">
                    <div class="space-y-8">
                        <!-- Dropzone Horizontal que ocupa todo el ancho -->
                        <div class="w-full">
                            <label class="w-full h-40 rounded-2xl border-2 border-dashed border-slate-700 hover:border-purple-500 hover:bg-purple-500/5 transition cursor-pointer flex flex-col items-center justify-center text-slate-500 hover:text-purple-400 group">
                                <input type="file" name="imagenes[]" x-ref="fileInput" @change="handleUpload" class="hidden" accept="image/*" multiple>
                                <div class="w-12 h-12 rounded-full bg-[#0f172a] flex items-center justify-center group-hover:bg-purple-600/20 mb-3 transition">
                                    <i class="fas fa-cloud-arrow-up text-xl"></i>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-widest">Haz clic o arrastra fotos aquí</span>
                                <span class="text-[10px] mt-1 text-slate-500" x-text="photos.length + ' de 15 subidas'"></span>
                            </label>
                            @error('imagenes')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                            @error('imagenes.*')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cuadrícula de miniaturas debajo -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4" x-show="photos.length > 0" x-transition>
                            <template x-for="(image, index) in photos" :key="index">
                                <div class="relative group aspect-square rounded-2xl overflow-hidden border border-slate-700 bg-[#0f172a]">
                                    <img :src="image.url" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <button @click.prevent="removePhoto(index)" type="button" class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center hover:scale-110 transition">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                    <div x-show="index === 0" class="absolute top-2 left-2 px-2 py-0.5 bg-blue-600 text-[8px] font-bold uppercase rounded shadow">Portada</div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="pt-10 pb-20">
                <button type="submit" class="w-full px-12 py-5 rounded-2xl bg-blue-600 font-extrabold text-xl text-white hover:bg-blue-700 shadow-2xl transition flex items-center justify-center group">
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

                    if (this.photos.length + files.length > 15) {
                        window.alert('Límite de 15 fotos.');
                        event.target.value = '';
                        return;
                    }

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
                    if (files.length > 0) {
                        files.splice(index, 1);
                        const transfer = new DataTransfer();
                        files.forEach((file) => transfer.items.add(file));
                        input.files = transfer.files;
                    }
                    
                    this.photos.splice(index, 1);
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
