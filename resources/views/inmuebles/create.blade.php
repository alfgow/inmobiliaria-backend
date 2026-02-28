<x-layouts.admin title="Nuevo inmueble">
    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm uppercase tracking-[0.3em] text-blue-600 dark:text-blue-400">Catálogo</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100 md:text-4xl">Registrar nuevo inmueble</h1>
            <p class="mt-3 max-w-2xl text-sm text-slate-500 dark:text-slate-400 md:text-base">
                Completa la información clave para compartirlo con tus prospectos. Puedes actualizar las fotos y detalles en cualquier momento.
            </p>
        </div>

        <form
            action="{{ route('inmuebles.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-8"
            data-swal-loader="registrar-inmueble"
            data-swal-loader-title="Registrando inmueble"
            data-swal-loader-text="Estamos guardando la información del inmueble..."
        >
            @csrf

            <x-inmuebles.form
                :tipos="$tipos"
                :operaciones="$operaciones"
                :watermark-preview-url="$watermarkPreviewUrl"
                :show-status-selector="false"
            />

            <div class="flex flex-col items-stretch gap-3 border-t border-slate-200 pt-6 dark:border-slate-700 sm:flex-row sm:justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">Al guardar, el inmueble se marcará como asignado a tu usuario.</p>
                <div class="flex gap-3">
                    <a href="{{ route('inmuebles.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700">Cancelar</a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-blue-200 transition hover:bg-blue-700 dark:shadow-blue-900/30">
                        Guardar inmueble
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
