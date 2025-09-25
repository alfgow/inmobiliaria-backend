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
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-gray-950 border-r border-gray-800 flex flex-col">
            <div class="p-4 text-2xl font-bold text-indigo-400">
                Inmobiliaria
            </div>

            <nav class="flex-1 p-4 space-y-2">
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
        <main class="flex-1 p-6 flex flex-col">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
