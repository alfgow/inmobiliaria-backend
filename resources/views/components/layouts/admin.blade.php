<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel de administración' }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-gray-950 border-r border-gray-800 flex flex-col">
            <div class="p-4 text-2xl font-bold text-indigo-400">
                Inmobiliaria
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-800">📊 Dashboard</a>
                <a href="{{ route('contactos.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800">🧑‍💼 Contactos</a>
                {{-- <a href="{{ route('inmuebles.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800">🏠 Inmuebles</a>
                <a href="{{ route('arrendadores.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800">👤 Arrendadores</a>
                
                <a href="{{ route('polizas.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800">📑 Pólizas</a>
                <a href="{{ route('blog.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800">📝 Blog</a>
                <a href="{{ route('finanzas.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800">💰 Finanzas</a> --}}
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
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
