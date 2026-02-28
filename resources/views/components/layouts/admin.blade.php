<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel de administración' }}</title>
    @php
        $hasViteManifest = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
    @endphp
    @if ($hasViteManifest)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased selection:bg-blue-100 selection:text-blue-900 dark:bg-slate-900 dark:text-slate-100 dark:selection:bg-blue-900 dark:selection:text-blue-100">
    @php
        $navLink = fn(string $route, string $label, string $icon, string|array $patterns) => [
            'url' => route($route),
            'label' => $label,
            'icon' => $icon,
            'active' => request()->routeIs(...(array) $patterns),
        ];

        $links = [
            $navLink('dashboard', 'Dashboard', 'layout-dashboard', 'dashboard'),
            $navLink('contactos.index', 'Contactos', 'users', 'contactos.*'),
            $navLink('inmuebles.index', 'Inmuebles', 'home', ['inmuebles.index', 'inmuebles.create', 'inmuebles.edit']),
            $navLink('inmuebles.map', 'Mapa', 'map-pin', 'inmuebles.map'),
        ];

        if (Auth::user()?->can('viewAny', \App\Models\User::class)) {
            $links[] = $navLink('users.index', 'Usuarios', 'users', 'users.*');
        }

        $activeLink = collect($links)->firstWhere('active', true);
    @endphp

    <div class="flex h-screen bg-slate-50 dark:bg-slate-900">
        {{-- Sidebar --}}
        <aside class="hidden lg:flex flex-col w-72 bg-white border-r border-slate-200 p-6 dark:bg-slate-800 dark:border-slate-700">
            {{-- Logo --}}
            <div class="flex items-center gap-3 mb-10 px-2">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200 dark:shadow-blue-900/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-slate-100">CRM INMO</h1>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-1">
                @foreach ($links as $link)
                    <a href="{{ $link['url'] }}" 
                        @class([
                            'flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 font-medium',
                            'bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/30' => $link['active'],
                            'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200' => !$link['active'],
                        ])>
                        @if($link['icon'] === 'layout-dashboard')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        @elseif($link['icon'] === 'users')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        @elseif($link['icon'] === 'home')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        @elseif($link['icon'] === 'map-pin')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @endif
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- User Section --}}
            <div class="pt-6 border-t border-slate-100 dark:border-slate-700">
                <a href="{{ route('profile.edit') }}" 
                    @class([
                        'flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 font-medium',
                        'bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/30' => request()->routeIs('profile.*'),
                        'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200' => !request()->routeIs('profile.*'),
                    ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Configuración</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Mobile Header --}}
        <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-slate-200 px-4 py-3 dark:bg-slate-800 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <span class="font-bold text-slate-900 dark:text-slate-100">CRM INMO</span>
                </div>
                <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-2 text-slate-500 dark:text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </header>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden lg:hidden fixed inset-0 z-50 bg-white pt-16 dark:bg-slate-800">
            <div class="p-4 space-y-2">
                @foreach ($links as $link)
                    <a href="{{ $link['url'] }}" 
                        @class([
                            'flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 font-medium',
                            'bg-blue-600 text-white' => $link['active'],
                            'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700' => !$link['active'],
                        ])>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden pt-14 lg:pt-0">
            {{-- Top Header --}}
            <header class="hidden lg:flex h-20 bg-white/80 backdrop-blur-md border-b border-slate-100 items-center justify-between px-8 z-10 dark:bg-slate-800/80 dark:border-slate-700">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest dark:text-slate-500">
                    <span>App</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-blue-600 dark:text-blue-400">{{ $activeLink['label'] ?? 'Dashboard' }}</span>
                </div>

                <div class="flex items-center gap-4">
                    {{-- Search --}}
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input type="text" placeholder="Buscar..." class="w-64 bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all dark:bg-slate-900 dark:border-slate-600 dark:text-slate-200 dark:placeholder-slate-500 dark:focus:ring-blue-900/50">
                    </div>

                    {{-- Notifications --}}
                    <button class="p-2 text-slate-400 hover:bg-slate-50 rounded-lg relative dark:text-slate-500 dark:hover:bg-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-800"></span>
                    </button>

                    {{-- User Avatar --}}
                    <div class="w-10 h-10 rounded-xl bg-slate-200 border-2 border-white overflow-hidden shadow-sm dark:bg-slate-700 dark:border-slate-600">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Usuario') }}&background=0D8ABC&color=fff" alt="Profile" class="w-full h-full object-cover">
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
