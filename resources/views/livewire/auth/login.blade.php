<x-layouts.guest-layout>
    <div class="flex flex-col items-center justify-center w-full min-h-screen px-4 bg-gray-900">

        <!-- Card -->
        <div class="w-full max-w-lg bg-gray-800 rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-white text-center mb-6">Iniciar sesión</h2>

            @if (session('status'))
                <div class="mb-4 text-sm text-green-400 text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="mt-1 block w-full rounded-lg border border-gray-700 bg-gray-700 text-white px-4 py-3
                               placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500/30 sm:text-sm">
                    @error('email')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300">Contraseña</label>
                    <input id="password" type="password" name="password" required
                        class="mt-1 block w-full rounded-lg border border-gray-700 bg-gray-700 text-white px-4 py-3
                               placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500/30 sm:text-sm">
                    @error('password')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-gray-400">
                        <input type="checkbox" name="remember"
                            class="h-4 w-4 text-indigo-600 border-gray-600 bg-gray-700 rounded focus:ring-indigo-500">
                        <span class="ml-2">Recordarme</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="text-indigo-400 hover:text-indigo-300" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <!-- Botón -->
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg shadow-md transition">
                    Entrar
                </button>
            </form>
        </div>
    </div>
</x-layouts.guest-layout>
