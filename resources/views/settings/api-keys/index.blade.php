<x-layouts.admin>
    <div class="mx-auto w-full max-w-4xl space-y-10">
        <div class="space-y-2 text-center">
            <p class="text-sm uppercase tracking-widest text-indigo-400">Integraciones</p>
            <h1 class="text-3xl font-bold text-white">API Keys</h1>
            <p class="text-gray-400">Administra credenciales vigentes, suspendidas o revocadas para integraciones externas.</p>
        </div>

        @if ($status)
            <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ $status }}
            </div>
        @endif

        @unless ($supportsLifecycle)
            <div class="rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                La vista ya quedó compatible, pero la tabla <span class="font-mono text-amber-50">api_keys</span> todavía no tiene las columnas nuevas.
                Aplica el SQL de <span class="font-mono text-amber-50">docs/sql/2026-04-20-api-keys-management.sql</span> para habilitar suspensión, rotación y estados vigentes.
            </div>
        @endunless

        @if ($createdKey)
            <div class="space-y-4 rounded-2xl border border-indigo-500/30 bg-indigo-500/10 p-5 text-sm text-indigo-100">
                <div>
                    <h2 class="mb-2 text-base font-semibold text-indigo-200">Nuevo access token generado</h2>
                    <p class="mb-3 text-indigo-100">
                        Copia y guarda este valor ahora; por seguridad no volverá a mostrarse.
                        Recomendado: <span class="font-mono text-indigo-200">X-Api-Key: &lt;valor&gt;</span>.
                        También aceptamos <span class="font-mono text-indigo-200">Authorization: Bearer &lt;valor&gt;</span> por compatibilidad.
                    </p>
                    <div class="overflow-x-auto rounded-xl border border-indigo-400/40 bg-gray-950/80 px-4 py-3 font-mono text-xs leading-relaxed md:text-sm">
                        {{ $createdKey['access_token'] }}
                    </div>
                </div>
                <div class="rounded-xl border border-indigo-400/20 bg-indigo-500/5 px-4 py-3 text-xs text-indigo-200">
                    <p class="font-semibold text-indigo-100">Prefijo de referencia</p>
                    <p class="font-mono text-indigo-100/90">{{ $createdKey['prefix'] }}</p>
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
            <div>
                <label for="allowed_ip" class="mb-2 block text-sm font-medium text-gray-300">IP autorizada (opcional)</label>
                <input
                    type="text"
                    id="allowed_ip"
                    name="allowed_ip"
                    value="{{ old('allowed_ip') }}"
                    placeholder="Ej. 192.0.2.10"
                    class="w-full rounded-xl border border-gray-700 bg-gray-850/70 px-4 py-3 text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                    @disabled(! $supportsAllowedIp)
                >
                <p class="mt-2 text-sm text-gray-400">
                    @if ($supportsAllowedIp)
                        Solo permitiremos solicitudes desde esta IP cuando se use la API key.
                    @else
                        Esta restricción quedará disponible cuando agregues la columna <span class="font-mono">allowed_ip</span> con el SQL de actualización.
                    @endif
                </p>
                @error('allowed_ip')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-500 px-5 py-3 font-medium text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                🔑
                <span>Generar nueva API key</span>
            </button>
        </form>

        <div class="space-y-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-white">API keys registradas</h2>
                    <p class="text-sm text-gray-400">
                        @if ($supportsLifecycle)
                            Solo las keys en estado <span class="font-semibold text-emerald-300">Vigente</span> pasan autenticación.
                        @else
                            Mientras no apliques el SQL, las keys existentes se mostrarán sin ciclo de vida avanzado.
                        @endif
                    </p>
                </div>
            </div>

            @if ($apiKeys->isEmpty())
                <p class="rounded-xl border border-gray-800 bg-gray-900/70 px-4 py-5 text-center text-gray-400">
                    Aún no has creado API keys. Usa el formulario de arriba para generar la primera.
                </p>
            @else
                <div class="space-y-4">
                    @foreach ($apiKeys as $apiKey)
                        @php
                            $statusClasses = match ($apiKey->status) {
                                \App\Models\ApiKey::STATUS_SUSPENDED => 'border-amber-500/40 bg-amber-500/10 text-amber-200',
                                \App\Models\ApiKey::STATUS_REVOKED => 'border-red-500/40 bg-red-500/10 text-red-200',
                                default => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200',
                            };
                        @endphp
                        <article class="rounded-2xl border border-gray-800 bg-gray-900/70 p-5 shadow-lg shadow-black/30">
                            <div class="flex flex-col gap-5">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h3 class="text-lg font-semibold text-gray-100">{{ $apiKey->name }}</h3>
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                                {{ $apiKey->statusLabel() }}
                                            </span>
                                        </div>
                                        <div class="grid gap-3 text-sm text-gray-300 md:grid-cols-2">
                                            <div class="space-y-1">
                                                <p class="text-xs uppercase tracking-wide text-gray-500">Identificador</p>
                                                <p class="font-mono">{{ $apiKey->maskedKey() }}</p>
                                            </div>
                                            <div class="space-y-1">
                                                <p class="text-xs uppercase tracking-wide text-gray-500">IP autorizada</p>
                                                <p class="font-mono">{{ $apiKey->allowed_ip ?? 'Sin restricción' }}</p>
                                            </div>
                                            <div class="space-y-1">
                                                <p class="text-xs uppercase tracking-wide text-gray-500">Creada</p>
                                                <p>{{ $apiKey->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                                            </div>
                                            <div class="space-y-1">
                                                <p class="text-xs uppercase tracking-wide text-gray-500">Último uso</p>
                                                <p>{{ optional($apiKey->last_used_at)->diffForHumans() ?? 'Nunca utilizada' }}</p>
                                            </div>
                                            @if ($supportsLifecycle && $apiKey->suspended_at)
                                                <div class="space-y-1">
                                                    <p class="text-xs uppercase tracking-wide text-gray-500">Suspendida desde</p>
                                                    <p>{{ $apiKey->suspended_at->format('d/m/Y H:i') }}</p>
                                                </div>
                                            @endif
                                            @if ($supportsLifecycle && $apiKey->revoked_at)
                                                <div class="space-y-1">
                                                    <p class="text-xs uppercase tracking-wide text-gray-500">Revocada desde</p>
                                                    <p>{{ $apiKey->revoked_at->format('d/m/Y H:i') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    @if ($supportsLifecycle && $apiKey->isActive())
                                        <form action="{{ route('settings.api-keys.suspend', $apiKey) }}" method="POST" onsubmit="return confirm('¿Suspender esta API key? Mientras esté suspendida no podrá autenticarse.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-amber-500/60 px-4 py-2 text-sm font-medium text-amber-200 transition hover:bg-amber-500/10">
                                                <span>Suspender</span>
                                            </button>
                                        </form>
                                    @elseif ($supportsLifecycle && $apiKey->isSuspended())
                                        <form action="{{ route('settings.api-keys.activate', $apiKey) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-emerald-500/60 px-4 py-2 text-sm font-medium text-emerald-200 transition hover:bg-emerald-500/10">
                                                <span>Reactivar</span>
                                            </button>
                                        </form>
                                    @endif

                                    @if ($supportsLifecycle && ! $apiKey->isRevoked())
                                        <form action="{{ route('settings.api-keys.rotate', $apiKey) }}" method="POST" onsubmit="return confirm('¿Rotar esta API key? Se generará una nueva y la actual quedará invalidada.');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-indigo-500/60 px-4 py-2 text-sm font-medium text-indigo-200 transition hover:bg-indigo-500/10">
                                                <span>Rotar</span>
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('settings.api-keys.destroy', $apiKey) }}" method="POST" onsubmit="return confirm('¿Eliminar esta API key? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-500/60 px-4 py-2 text-sm font-medium text-red-200 transition hover:bg-red-500/10">
                                            <span>Eliminar</span>
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
