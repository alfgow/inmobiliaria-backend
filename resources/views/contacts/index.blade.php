<x-layouts.admin>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Contactos</h1>
            <p class="text-gray-400 mt-1">
                Revisa los mensajes enviados desde el formulario de contacto.
            </p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-850/50 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-800">
            <thead class="bg-gray-900/60">
                <tr class="text-left text-sm uppercase tracking-wide text-gray-400">
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Correo</th>
                    <th class="px-4 py-3">Teléfono</th>
                    <th class="px-4 py-3">Mensaje</th>
                    <th class="px-4 py-3">Recibido</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-sm">
                @forelse ($contacts as $contact)
                    <tr class="hover:bg-gray-900/40 transition">
                        <td class="px-4 py-3 font-mono text-xs text-gray-400">#{{ $contact->id }}</td>
                        <td class="px-4 py-3 font-medium text-gray-200">
                            {{ $contact->name ?? $contact->nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-gray-300">{{ $contact->email ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-gray-300">{{ $contact->phone ?? $contact->telefono ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 max-w-xs">
                            <p class="text-gray-300 line-clamp-2">
                                {{ $contact->message ?? $contact->mensaje ?? '—' }}
                            </p>
                        </td>
                        <td class="px-4 py-3 text-gray-400">
                            {{ optional($contact->created_at)->format('d/m/Y H:i') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            No hay registros de contacto por el momento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $contacts->links() }}
    </div>
</x-layouts.admin>
