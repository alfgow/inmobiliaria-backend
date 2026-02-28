<x-layouts.admin title="Mapa de inmuebles">
    <div class="space-y-6">
        <header class="space-y-2">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Mapa de inmuebles</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Visualiza la ubicación de los inmuebles registrados y accede rápidamente a su edición.
            </p>
        </header>

        <div
            id="properties-map"
            class="h-[600px] w-full overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-900"
            data-properties='@json($properties)'
        ></div>
    </div>

    @vite('resources/js/app.js')
</x-layouts.admin>
