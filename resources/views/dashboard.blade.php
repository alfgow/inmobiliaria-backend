<x-layouts.admin>
    <h1 class="text-2xl md:text-3xl font-bold mb-6">👋Hola {{ Auth::user()->name ?? 'Usuario' }}</h1>
    <!-- Tarjetas KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white/5 backdrop-blur-md border border-white/20 rounded-2xl p-5 shadow-lg flex flex-col items-center text-center">
            <div class="text-indigo-400 text-sm mb-1">Inmuebles activos</div>
            <div class="text-3xl font-bold text-indigo-300">0</div>
        </div>
        <div class="bg-white/5 backdrop-blur-md border border-white/20 rounded-2xl p-5 shadow-lg flex flex-col items-center text-center">
            <div class="text-emerald-400 text-sm mb-1">Interesados hoy</div>
            <div class="text-3xl font-bold text-emerald-300">0</div>
        </div>
        <div class="bg-white/5 backdrop-blur-md border border-white/20 rounded-2xl p-5 shadow-lg flex flex-col items-center text-center">
            <div class="text-pink-400 text-sm mb-1">Publicaciones del blog</div>
            <div class="text-3xl font-bold text-pink-300">0</div>
        </div>
    </div>

    <!-- Gráfica -->
    <div class="bg-white/5 backdrop-blur-md border border-white/20 rounded-2xl p-6 shadow-lg">
        <h2 class="text-lg font-semibold text-gray-200 mb-4">Actividad mensual</h2>
        <canvas id="chartDashboard"></canvas>
    </div>
</x-layouts.admin>
