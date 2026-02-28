<x-layouts.admin>
    <section class="space-y-8">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Resumen ejecutivo</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">👋 Hola {{ Auth::user()->name ?? 'Usuario' }}</h1>
                <p class="mt-2 text-sm text-slate-500">Monitorea ventas, oportunidades y el rendimiento comercial en tiempo real.</p>
            </div>
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                <span>+ Nueva propiedad</span>
            </button>
        </div>

        @php
            $kpis = [
                ['title' => 'Ventas del mes', 'value' => '$3,420,000', 'trend' => '+12.5%', 'accent' => 'emerald'],
                ['title' => 'Inmuebles activos', 'value' => '48', 'trend' => '+4', 'accent' => 'indigo'],
                ['title' => 'Leads por atender', 'value' => '12', 'trend' => '+3', 'accent' => 'amber'],
            ];
        @endphp

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($kpis as $kpi)
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500">{{ $kpi['title'] }}</p>
                            <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $kpi['value'] }}</h3>
                        </div>
                        <span
                            class="rounded-xl px-2.5 py-1 text-xs font-bold uppercase tracking-wide {{ $kpi['accent'] === 'emerald' ? 'bg-emerald-50 text-emerald-600' : ($kpi['accent'] === 'amber' ? 'bg-amber-50 text-amber-600' : 'bg-indigo-50 text-indigo-600') }}">
                            {{ $kpi['trend'] }}
                        </span>
                    </div>
                    <p class="mt-5 text-xs font-medium text-slate-400">Comparativo respecto al último mes.</p>
                </article>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-800">Actividad mensual</h2>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">Últimos 6 meses</span>
                </div>
                <canvas id="chartDashboard"></canvas>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-800">Actividad reciente</h2>
                    <a href="#" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Ver todo</a>
                </div>
                <div class="space-y-4">
                    @foreach ([
                        'Juan Pérez agendó una visita para Penthouse en Polanco.',
                        'María Gómez solicitó información de Casa Santa Fe.',
                        'Nueva oportunidad entrante en Roma Norte.',
                    ] as $activity)
                        <article class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-sm font-medium text-slate-700">{{ $activity }}</p>
                            <p class="mt-1 text-xs text-slate-400">Hace unos minutos</p>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </section>
</x-layouts.admin>
