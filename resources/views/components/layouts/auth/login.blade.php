<x-guest-layout>
    <div class="w-full max-w-md bg-gray-800 rounded-2xl p-8 shadow-lg">

        <!-- Título -->
        <h2 class="text-center text-2xl font-bold text-white mb-6">
            Iniciar sesión
        </h2>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 text-sm text-green-400 text-center">
                {{ session('status') }}
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-white 
                           placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('email')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300">Contraseña</label>
                <input id="password" type="password" name="password" required
                    class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-white 
                           placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('password')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember + Forgot -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-gray-400">
                    <input type="checkbox" name="remember"
                        class="h-4 w-4 text-indigo-600 border-gray-600 bg-gray-700 rounded">
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
                class="w-full bg-indigo-600 text-white py-2 rounded-lg shadow hover:bg-indigo-700 transition">
                Entrar
            </button>
        </form>
    </div>
</x-guest-layout>
