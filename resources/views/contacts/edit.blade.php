<x-layouts.admin>
    <div class="flex flex-1 items-center justify-center">
        <div class="w-full max-w-2xl space-y-8">
            <header class="text-center space-y-2">
                <p class="text-sm uppercase tracking-widest text-indigo-400">Editar contacto</p>
                <h1 class="text-2xl md:text-3xl font-bold">Actualizar información</h1>
                <p class="text-gray-400">Modifica los datos del contacto y guarda los cambios.</p>
            </header>

            <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6 shadow-xl shadow-black/30">
                <form
                    action="{{ route('contactos.update', $contact) }}"
                    method="POST"
                    class="space-y-6"
                    data-swal-loader="actualizar-contacto"
                >
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="nombre" class="block text-sm font-medium text-gray-300">Nombre completo<span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="{{ old('nombre', $contact->nombre) }}"
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
                            value="{{ old('email', $contact->email) }}"
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
                            value="{{ old('telefono', $contact->telefono) }}"
                            class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            placeholder="5512345678"
                        >
                        @error('telefono')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('contactos.show', $contact) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-700 px-5 py-3 text-sm font-medium text-gray-300 transition hover:bg-gray-800/60">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
