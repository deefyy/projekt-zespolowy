@php
    $currentSort = request('sort');
    $currentDirection = request('direction') ?? 'asc';

    function sortIcon($column) {
        $sort = request('sort');
        $direction = request('direction', 'asc');
        return $sort === $column ? ($direction === 'asc' ? '▲' : '▼') : '↕';
    }

    function sortLink($column, $label) {
        $currentSort = request('sort');
        $currentDirection = request('direction') ?? 'asc';
        $nextDirection = ($currentSort === $column && $currentDirection === 'asc') ? 'desc' : 'asc';
        $search = request('search');
        $url = url()->current() . "?sort={$column}&direction={$nextDirection}&search={$search}";
        return "<a href=\"{$url}\" class=\"hover:underline font-semibold text-[#002d62]\">{$label} <span>" . sortIcon($column) . "</span></a>";
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[#002d62] text-center">Zarządzanie użytkownikami</h2>
            </div>
        </header>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Wyszukiwarka + przycisk --}}
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-4 items-center">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Szukaj użytkownika..."
                           class="border border-[#cdd7e4] rounded-xl px-4 py-2 w-full sm:w-80 shadow-sm">
                    <button type="submit"
                            class="bg-[#002d62] text-white px-5 py-2 rounded-xl hover:bg-[#001b3e] transition">
                        🔍 Szukaj
                    </button>
                </form>

                <a href="{{ route('admin.createUser') }}"
                   class="inline-block bg-[#002d62] text-white px-6 py-3 rounded-xl hover:bg-[#001b3e] transition font-semibold">
                    ➕ Dodaj użytkownika
                </a>
            </div>

            {{-- Komunikat sukcesu --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabela użytkowników --}}
            <div class="bg-white shadow-sm border border-[#cdd7e4] rounded-2xl overflow-hidden">
                <table class="w-full table-auto text-sm text-center">
                    <thead class="bg-[#f1f5fb] text-[#002d62]">
                        <tr>
                            <th class="px-4 py-3">{!! sortLink('name', 'Imię') !!}</th>
                            <th class="px-4 py-3">{!! sortLink('last_name', 'Nazwisko') !!}</th>
                            <th class="px-4 py-3">{!! sortLink('email', 'Email') !!}</th>
                            <th class="px-4 py-3">{!! sortLink('role', 'Rola') !!}</th>
                            <th class="px-4 py-3">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf2f7]">
                        @forelse($users as $user)
                            <tr class="hover:bg-[#f9fbfd]">
                                <td class="px-4 py-3">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->last_name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">{{ ucfirst($user->role) }}</td>
                                <td class="px-4 py-3 space-x-2">
                                    <a href="{{ route('admin.editUser', $user->id) }}"
                                       class="text-blue-600 hover:underline">Edytuj</a>

                                    @if(auth()->id() !== $user->id)
                                        <button type="button"
                                                onclick="document.getElementById('modal-{{ $user->id }}').classList.remove('hidden')"
                                                class="text-red-600 hover:underline">Usuń</button>

                                        {{-- MODAL --}}
                                        <div id="modal-{{ $user->id }}"
                                             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                                                <form method="POST" action="{{ route('admin.deleteUser', $user->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <h3 class="text-lg font-bold mb-2">Potwierdzenie usunięcia</h3>
                                                    <p>Czy na pewno chcesz usunąć użytkownika <strong>{{ $user->name }} {{ $user->last_name }}</strong>?</p>
                                                    <div class="mt-6 flex justify-end gap-3">
                                                        <button type="button"
                                                                onclick="document.getElementById('modal-{{ $user->id }}').classList.add('hidden')"
                                                                class="px-4 py-2 border rounded hover:bg-gray-100">
                                                            Anuluj
                                                        </button>
                                                        <button type="submit"
                                                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                                            Usuń
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-gray-500 italic">Brak użytkowników do wyświetlenia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Paginacja --}}
                <div class="p-4">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
