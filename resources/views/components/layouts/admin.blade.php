<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel de administración' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
</head>

<body class="min-h-screen bg-slate-100 text-slate-900 antialiased selection:bg-indigo-100 selection:text-indigo-900">
    @php
        $navLink = fn(string $route, string $label, string $icon, string|array $patterns) => [
            'url' => route($route),
            'label' => $label,
            'icon' => $icon,
            'active' => request()->routeIs(...(array) $patterns),
        ];

        $links = [
            $navLink('dashboard', 'Dashboard', '📊', 'dashboard'),
            $navLink('contactos.index', 'Contactos', '🧑‍💼', 'contactos.*'),
            $navLink('inmuebles.index', 'Inmuebles', '🏠', ['inmuebles.index', 'inmuebles.create', 'inmuebles.edit']),
            $navLink('inmuebles.map', 'Mapa de inmuebles', '🗺️', 'inmuebles.map'),
            $navLink('settings.api-keys.index', 'API Keys', '🔑', 'settings.api-keys.*'),
        ];

        if (Auth::user()?->can('viewAny', \App\Models\User::class)) {
            $links[] = $navLink('users.index', 'Usuarios', '🧑‍🤝‍🧑', 'users.*');
        }

        $activeLink = collect($links)->firstWhere('active', true);
    @endphp

    <div class="relative min-h-screen">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.14),_transparent_45%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.15),_transparent_45%)]"></div>

        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">CRM Inmobiliario</p>
                    <p class="text-base font-black text-slate-900">Inmobiliaria</p>
                </div>
                <button type="button" data-sidebar-open
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                    <span class="sr-only">Abrir menú de navegación</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </header>

        <div data-sidebar-backdrop
            class="fixed inset-0 z-[900] bg-slate-900/50 opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden"></div>

        <div class="flex min-h-screen">
            <aside data-sidebar
                class="fixed inset-y-0 left-0 z-[1000] flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white px-5 py-6 shadow-2xl shadow-indigo-950/10 transition-transform duration-300 lg:static lg:z-auto lg:translate-x-0 lg:shadow-none">
                <div class="mb-8 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-indigo-600 to-sky-500 text-white shadow-lg shadow-indigo-200">
                            <span class="text-lg">🏡</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Panel de control</p>
                            <h1 class="text-lg font-black tracking-tight">CRM INMO</h1>
                        </div>
                    </div>
                    <button type="button" data-sidebar-close
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-2 text-slate-600 transition hover:bg-slate-50 lg:hidden">
                        <span class="sr-only">Cerrar menú</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-1.5 overflow-y-auto pr-1">
                    @foreach ($links as $link)
                        <a href="{{ $link['url'] }}" data-sidebar-link
                            @class([
                                'group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-200',
                                'bg-gradient-to-r from-indigo-600 to-sky-500 text-white shadow-lg shadow-indigo-200' => $link['active'],
                                'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => !$link['active'],
                            ])>
                            <span class="text-base">{{ $link['icon'] }}</span>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Sesión activa</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ Auth::user()->name ?? 'Usuario' }}</p>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button
                            class="w-full rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-30 hidden h-20 items-center justify-between border-b border-slate-200/70 bg-white/80 px-6 backdrop-blur-lg lg:flex xl:px-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">App / Panel</p>
                        <p class="text-sm font-semibold text-slate-700">{{ $activeLink['label'] ?? 'Dashboard' }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative hidden xl:block">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 5.85 5.85a7.5 7.5 0 0 0 10.8 10.8Z" />
                                </svg>
                            </span>
                            <input type="text" placeholder="Buscar cliente o inmueble..."
                                class="w-72 rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none ring-indigo-100 transition focus:ring-4" />
                        </label>
                        <button
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50">
                            <span class="sr-only">Notificaciones</span>
                            🔔
                        </button>
                        <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-1.5">
                            <div class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-100 text-xs font-bold text-indigo-700">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="text-sm font-semibold text-slate-700">{{ Auth::user()->name ?? 'Usuario' }}</span>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</body>

</html>
