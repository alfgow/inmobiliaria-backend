<x-layouts.admin title="Nuevo inmueble">
    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-8">
        <div class="rounded-3xl border border-gray-800 bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950 p-8 text-white shadow-2xl shadow-black/30">
            <p class="text-sm uppercase tracking-[0.3em] text-indigo-300">Catálogo</p>
            <h1 class="mt-2 text-3xl font-semibold md:text-4xl">Registrar nuevo inmueble</h1>
            <p class="mt-3 max-w-2xl text-sm text-gray-300 md:text-base">
                Completa la información clave para compartirlo con tus prospectos. Puedes actualizar las fotos y detalles en cualquier momento.
            </p>
        </div>

        <form action="{{ route('inmuebles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <x-inmuebles.form :statuses="$statuses" :tipos="$tipos" :operaciones="$operaciones" />

            <div class="flex flex-col items-stretch gap-3 border-t border-gray-800 pt-6 sm:flex-row sm:justify-between">
                <p class="text-sm text-gray-400">Al guardar, el inmueble se marcará como asignado a tu usuario.</p>
                <div class="flex gap-3">
                    <a href="{{ route('inmuebles.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-700 px-5 py-3 text-sm font-medium text-gray-300 transition hover:border-gray-500 hover:text-white">Cancelar</a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                        Guardar inmueble
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
