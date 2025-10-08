<x-layouts.admin>
    <div class="mx-auto w-full max-w-4xl space-y-10">
        <div class="space-y-2 text-center">
            <p class="text-sm uppercase tracking-widest text-indigo-400">Integraciones</p>
            <h1 class="text-3xl font-bold text-white">API Keys</h1>
            <p class="text-gray-400">Genera tokens de acceso para servicios externos (por ejemplo AWS o automatizaciones).</p>
        </div>

        @if ($status)
            <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ $status }}
            </div>
        @endif

        @if ($createdKey)
            <div class="rounded-2xl border border-indigo-500/30 bg-indigo-500/10 p-5 text-sm text-indigo-100">
                <h2 class="mb-2 text-base font-semibold text-indigo-200">Nueva API key creada</h2>
                <p class="mb-3 text-indigo-100">Copia y guarda este valor ahora; por seguridad no volverá a mostrarse.</p>
                <div class="rounded-xl border border-indigo-400/40 bg-gray-950/80 px-4 py-3 font-mono text-base">
                    {{ $createdKey['key'] }}
                </div>
            </div>
        @endif

        <form action="{{ route('settings.api-keys.store') }}" method="POST" class="space-y-4 rounded-2xl border border-gray-800 bg-gray-900/70 p-6 shadow-xl shadow-black/30">
            @csrf
            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-gray-300">Nombre para identificar la API key</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Ej. AWS Lambda producción"
                    class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                    required
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-500 px-5 py-3 font-medium text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                🔑
                <span>Generar nueva API key</span>
            </button>
        </form>

        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-white">API keys existentes</h2>

            @if ($apiKeys->isEmpty())
                <p class="rounded-xl border border-gray-800 bg-gray-900/70 px-4 py-5 text-center text-gray-400">
                    Aún no has creado API keys. Usa el formulario de arriba para generar la primera.
                </p>
            @else
                <div class="space-y-4">
                    @foreach ($apiKeys as $apiKey)
                        <article class="rounded-2xl border border-gray-800 bg-gray-900/70 p-5 shadow-lg shadow-black/30">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div class="space-y-1">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Nombre</p>
                                    <h3 class="text-lg font-semibold text-gray-100">{{ $apiKey->name }}</h3>
                                </div>
                                <div class="space-y-1 text-gray-300">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Identificador</p>
                                    <p class="font-mono text-sm">{{ $apiKey->maskedKey() }}</p>
                                </div>
                                <div class="space-y-1 text-gray-400">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Último uso</p>
                                    <p>{{ optional($apiKey->last_used_at)->diffForHumans() ?? 'Nunca utilizada' }}</p>
                                </div>
                                <div>
                                    <form action="{{ route('settings.api-keys.destroy', $apiKey) }}" method="POST" onsubmit="return confirm('¿Revocar esta API key? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-500/60 px-4 py-2 text-sm font-medium text-red-200 transition hover:bg-red-500/10">
                                            ❌
                                            <span>Revocar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
