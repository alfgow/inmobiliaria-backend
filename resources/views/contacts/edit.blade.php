<x-layouts.admin>
    <div class="flex flex-1 items-center justify-center">
        <div class="w-full max-w-2xl space-y-8">
            <header class="text-center space-y-2">
                <p class="text-sm uppercase tracking-widest text-blue-600 dark:text-blue-400">Editar contacto</p>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">Actualizar información</h1>
                <p class="text-slate-500 dark:text-slate-400">Modifica los datos del contacto y guarda los cambios.</p>
            </header>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <form
                    action="{{ route('contactos.update', $contact) }}"
                    method="POST"
                    class="space-y-6"
                    data-swal-loader="actualizar-contacto"
                >
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="nombre" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nombre completo<span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="{{ old('nombre', $contact->nombre) }}"
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
                            value="{{ old('email', $contact->email) }}"
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
                            value="{{ old('telefono', $contact->telefono) }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:placeholder-slate-500 dark:focus:ring-blue-900/50"
                            placeholder="5512345678"
                        >
                        @error('telefono')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('contactos.show', $contact) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-md shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/30">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
