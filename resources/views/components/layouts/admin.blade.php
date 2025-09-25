<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel de administración' }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="min-h-screen flex flex-col bg-gray-900">
        <!-- Mobile header -->
        <header class="md:hidden sticky top-0 z-30 bg-gray-950 border-b border-gray-800">
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-lg font-semibold text-indigo-400">Inmobiliaria</span>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-700 bg-gray-900/40 px-3 py-2 text-sm font-medium text-gray-200 shadow-sm transition hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                    data-sidebar-open
                >
                    <span class="sr-only">Abrir menú de navegación</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </header>

        <div data-sidebar-backdrop
            class="fixed inset-0 z-30 bg-black/50 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"></div>

        <div class="flex flex-1 md:flex-row">
            <!-- Sidebar -->
            <aside
                class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-950 border-r border-gray-800 flex flex-col transform -translate-x-full transition-transform duration-300 md:static md:inset-auto md:z-auto md:translate-x-0 md:transform-none"
                data-sidebar
            >
                <div class="flex items-center justify-between p-4 text-2xl font-bold text-indigo-400 md:block">
                    <span>Inmobiliaria</span>
                    <button
                        type="button"
                        class="md:hidden inline-flex items-center justify-center rounded-lg border border-gray-700 bg-gray-900/40 p-2 text-sm text-gray-200 transition hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                        data-sidebar-close
                    >
                        <span class="sr-only">Cerrar menú</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                @php
                    $navLink = fn (string $route, string $label, string $pattern) => [
                        'url' => route($route),
                        'label' => $label,
                        'active' => request()->routeIs($pattern),
                    ];

                    $links = [
                        $navLink('dashboard', '📊 Dashboard', 'dashboard'),
                        $navLink('contactos.index', '🧑‍💼 Contactos', 'contactos.*'),
                        $navLink('inmuebles.index', '🏠 Inmuebles', 'inmuebles.*'),
                    ];
                @endphp

                @foreach ($links as $link)
                    <a
                        href="{{ $link['url'] }}"
                        data-sidebar-link
                        @class([
                            'block rounded-xl px-3 py-2 transition',
                            'bg-gray-800 text-white shadow-lg shadow-indigo-500/10' => $link['active'],
                            'hover:bg-gray-800/80 text-gray-300' => ! $link['active'],
                        ])
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <!-- Usuario / Logout -->
            <div class="p-4 border-t border-gray-800">
                <span class="block text-sm text-gray-400 mb-2">Hola, {{ Auth::user()->name ?? 'Usuario' }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded">
                        Cerrar sesión
                    </button>
                </form>
            </div>
            </aside>

            <!-- Contenido -->
            <main class="flex-1 p-4 sm:p-6 md:p-8 flex flex-col">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
