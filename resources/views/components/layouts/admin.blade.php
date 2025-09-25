<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Inmobiliaria') }}</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-900 text-gray-100">
    <!-- Topbar simple -->
    <header class="w-full border-b border-gray-800 bg-gray-900/80 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="font-semibold">Inmobiliaria — Admin</div>

            <!-- Cerrar sesión -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="text-sm px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 transition"
                >
                    Cerrar sesión
                </button>
            </form>
        </div>
    </header>

    <!-- Contenido -->
    <main class="max-w-6xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>
</body>
</html>
