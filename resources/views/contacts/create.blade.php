@php use Illuminate\Support\Str; @endphp

<x-layouts.admin>
    <div class="flex flex-1 items-center justify-center">
        <div class="w-full max-w-2xl space-y-8">
            <header class="text-center space-y-2">
                <p class="text-sm uppercase tracking-widest text-indigo-400">Nuevo contacto</p>
                <h1 class="text-2xl md:text-3xl font-bold">Registrar contacto</h1>
                <p class="text-gray-400">Completa los datos para agregar un nuevo contacto al sistema.</p>
            </header>

            <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6 shadow-xl shadow-black/30">
                <form action="{{ route('contactos.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="inmueble_id" class="block text-sm font-medium text-gray-300">Inmueble asociado</label>
                        <div class="space-y-2" data-searchable-select>
                            <input
                                type="search"
                                id="inmueble-search"
                                data-search-input
                                placeholder="Buscar por título o dirección"
                                class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                autocomplete="off"
                            >
                            <select
                                id="inmueble_id"
                                name="inmueble_id"
                                class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            >
                                <option value="">Sin inmueble asociado</option>
                                @foreach ($inmuebles as $inmueble)
                                    <option
                                        value="{{ $inmueble->id }}"
                                        data-searchable="{{ Str::lower($inmueble->titulo . ' ' . $inmueble->direccion) }}"
                                        @selected((string) old('inmueble_id') === (string) $inmueble->id)
                                    >
                                        {{ $inmueble->titulo }} — {{ $inmueble->direccion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-sm text-gray-400">Utiliza el buscador para filtrar inmuebles por título o dirección.</p>
                        @error('inmueble_id')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="nombre" class="block text-sm font-medium text-gray-300">Nombre completo<span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="{{ old('nombre', $prefill) }}"
                            required
                            class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            placeholder="Ej. Juan Pérez"
                        >
                        @error('nombre')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-gray-300">Correo electrónico</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            placeholder="correo@dominio.com"
                        >
                        @error('email')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="telefono" class="block text-sm font-medium text-gray-300">Teléfono</label>
                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            value="{{ old('telefono') }}"
                            class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            placeholder="5512345678"
                        >
                        @error('telefono')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="mensaje" class="block text-sm font-medium text-gray-300">Notas o mensaje</label>
                        <textarea
                            id="mensaje"
                            name="mensaje"
                            rows="4"
                            class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            placeholder="Información adicional"
                        >{{ old('mensaje') }}</textarea>
                        @error('mensaje')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('contactos.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-700 px-5 py-3 text-sm font-medium text-gray-300 transition hover:bg-gray-800/60">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                            Guardar contacto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
