<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Throwable;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'agent';
    public bool $is_active = true;

    /** @var \Illuminate\Support\Collection<int, User> */
    public $users;

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);

        $this->loadUsers();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'agent'])],
            'is_active' => ['boolean'],
        ];
    }

    public function loadUsers(): void
    {
        $this->users = User::query()
            ->orderBy('name')
            ->get();
    }

    public function createUser(): void
    {
        Gate::authorize('create', User::class);

        $validated = $this->validate();

        try {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'is_active' => $validated['is_active'],
            ]);

            $this->reset(['name', 'email', 'password']);
            $this->role = 'agent';
            $this->is_active = true;

            $this->loadUsers();

            $this->dispatch('user-created', name: $validated['name']);
        } catch (Throwable $exception) {
            report($exception);

            $this->dispatch(
                'user-creation-failed',
                message: 'No fue posible registrar al usuario. Intenta nuevamente.'
            );
        }
    }

    public function toggleStatus(int $userId): void
    {
        $user = User::findOrFail($userId);

        Gate::authorize('toggleStatus', $user);

        $user->is_active = ! $user->is_active;
        $user->save();

        $this->loadUsers();

        $this->dispatch('user-status-updated');
    }
};
?>

<x-layouts.admin title="Usuarios">
    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-8">
        <div class="rounded-3xl border border-gray-800 bg-gray-900 p-8 shadow-2xl shadow-indigo-500/10">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-indigo-300">Equipo</p>
                    <h1 class="mt-2 text-3xl font-semibold md:text-4xl">Gestión de usuarios</h1>
                    <p class="mt-3 max-w-3xl text-sm text-gray-300 md:text-base">
                        Da de alta a nuevos integrantes, asigna sus roles y activa o desactiva el acceso cuando sea necesario.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[2fr,3fr]">
            <div class="rounded-3xl border border-gray-800 bg-gray-900/80 p-6 shadow-xl shadow-black/20">
                <h2 class="text-xl font-semibold text-white">Crear nuevo usuario</h2>
                <p class="mt-1 text-sm text-gray-400">Completa la información y asigna una contraseña temporal.</p>

                <form wire:submit.prevent="createUser" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Nombre completo</label>
                        <input
                            type="text"
                            wire:model.defer="name"
                            class="mt-1 w-full rounded-2xl border border-gray-700 bg-gray-900/60 px-4 py-3 text-sm text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            placeholder="Ej. Ana López"
                            autocomplete="name"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300">Correo electrónico</label>
                        <input
                            type="email"
                            wire:model.defer="email"
                            class="mt-1 w-full rounded-2xl border border-gray-700 bg-gray-900/60 px-4 py-3 text-sm text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            placeholder="Ej. ana@inmobiliaria.mx"
                            autocomplete="email"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300">Contraseña provisional</label>
                        <input
                            type="password"
                            wire:model.defer="password"
                            class="mt-1 w-full rounded-2xl border border-gray-700 bg-gray-900/60 px-4 py-3 text-sm text-gray-100 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            placeholder="Mínimo 8 caracteres"
                            autocomplete="new-password"
                            required
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Rol</label>
                            <select
                                wire:model.defer="role"
                                class="mt-1 w-full rounded-2xl border border-gray-700 bg-gray-900/60 px-4 py-3 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                required
                            >
                                <option value="agent">Agente</option>
                                <option value="admin">Administrador</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-end gap-3">
                            <label class="flex items-center gap-3 text-sm font-medium text-gray-300">
                                <input type="checkbox" wire:model.defer="is_active" class="h-5 w-5 rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-indigo-500">
                                Activar acceso inmediato
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400"
                        >
                            Guardar usuario
                        </button>
                        <x-action-message class="text-sm text-emerald-400" on="user-created">
                            Usuario creado correctamente.
                        </x-action-message>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl border border-gray-800 bg-gray-900/80 p-6 shadow-xl shadow-black/20">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">Usuarios registrados</h2>
                    <x-action-message class="text-sm text-emerald-400" on="user-status-updated">
                        Estado actualizado.
                    </x-action-message>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-800 text-sm">
                        <thead class="bg-gray-900/60 text-xs uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Nombre</th>
                                <th class="px-4 py-3 text-left">Correo</th>
                                <th class="px-4 py-3 text-left">Rol</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @forelse ($users as $user)
                                <tr class="bg-gray-900/40">
                                    <td class="px-4 py-3 text-gray-100">
                                        <div class="font-semibold">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">Alta: {{ optional($user->created_at)->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-300">{{ $user->email }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-300">
                                            {{ $user->role === 'admin' ? 'Administrador' : 'Agente' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($user->is_active)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-3 py-1 text-xs font-semibold text-rose-300">
                                                <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if (auth()->id() !== $user->id)
                                            <button
                                                wire:click="toggleStatus({{ $user->id }})"
                                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-700 px-4 py-2 text-xs font-semibold text-gray-200 transition hover:border-indigo-500 hover:text-white"
                                            >
                                                {{ $user->is_active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-500">No disponible para tu cuenta</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">
                                        Aún no hay usuarios registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
