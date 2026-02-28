<x-layouts.admin>
    {{-- Header Section --}}
    <header class="mb-8">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
            <span>App</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-blue-600">Dashboard</span>
        </div>
        <h2 class="text-3xl font-black text-slate-900">Dashboard</h2>
    </header>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Card 1 --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-500 font-medium">Ventas del mes</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">$3,420,000</h3>
                </div>
                <div class="p-2 rounded-lg bg-green-50 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-4">
                <span class="text-green-500 font-medium">+12.5%</span> vs el mes pasado
            </p>
        </div>

        {{-- Card 2 --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-500 font-medium">Propiedades activas</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">48</h3>
                </div>
                <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-4">
                <span class="text-green-500 font-medium">+4</span> vs el mes pasado
            </p>
        </div>

        {{-- Card 3 --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-500 font-medium">Leads por atender</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">12</h3>
                </div>
                <div class="p-2 rounded-lg bg-amber-50 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-4">
                <span class="text-amber-500 font-medium">+3</span> vs el mes pasado
            </p>
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Recent Activity --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-slate-800">Actividad Reciente</h3>
                <a href="#" class="text-blue-600 text-sm font-semibold hover:text-blue-700">Ver Todo</a>
            </div>
            <div class="space-y-4">
                @php
                    $activities = [
                        ['user' => 'Juan Pérez', 'action' => 'agendó una visita para', 'target' => 'Penthouse Polanco', 'time' => 'hace 15 minutos'],
                        ['user' => 'María González', 'action' => 'solicitó información de', 'target' => 'Casa Santa Fe', 'time' => 'hace 1 hora'],
                        ['user' => 'Carlos Ruiz', 'action' => 'mostró interés en', 'target' => 'Loft Roma Norte', 'time' => 'hace 3 horas'],
                    ];
                @endphp
                @foreach($activities as $activity)
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700">
                                <span class="font-bold text-slate-900">{{ $activity['user'] }}</span> 
                                {{ $activity['action'] }} 
                                <span class="font-bold text-slate-900">{{ $activity['target'] }}</span>
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $activity['time'] }}</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-300 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Sales Pipeline Chart --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-slate-800">Pipeline de Ventas</h3>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <div class="h-48 flex items-end justify-between gap-4 pb-2">
                @php $bars = [40, 70, 45, 90, 60, 80]; @endphp
                @foreach($bars as $i => $height)
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full bg-blue-50 rounded-t-lg relative group cursor-pointer" style="height: 100%;">
                            <div class="absolute bottom-0 w-full bg-blue-600 rounded-t-lg transition-all duration-500 hover:bg-blue-700" style="height: {{ $height }}%;"></div>
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ $height }}%
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <span>Ene</span>
                <span>Feb</span>
                <span>Mar</span>
                <span>Abr</span>
                <span>May</span>
                <span>Jun</span>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-8 flex flex-wrap gap-4">
        <a href="{{ route('inmuebles.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-blue-200 transition-all active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nueva Propiedad</span>
        </a>
        <a href="{{ route('contactos.create') }}" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-5 py-2.5 rounded-xl font-bold text-sm transition-all active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Nuevo Contacto</span>
        </a>
    </div>
</x-layouts.admin>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Aquí puedes agregar gráficos más complejos con Chart.js si lo necesitas
</script>
@endpush
