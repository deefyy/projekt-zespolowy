@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
    </div>
@endif

<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-center text-muted">
            {{ __('Panel Administratora') }}
        </h2>
    </x-slot>

    <div class="container py-4">
        {{-- Górna sekcja: wyszukiwarka + dodawanie użytkownika --}}
        <div class="row mb-4 justify-content-between align-items-center">
            <div class="col-md-7">
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Szukaj użytkownika..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">Szukaj</button>
                    </div>
                </form>
            </div>
            <div class="col-md-auto mt-3 mt-md-0 text-md-end">
                <a href="{{ route('admin.createUser') }}" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> Dodaj użytkownika
                </a>
            </div>
        </div>

        {{-- Tabela użytkowników --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Imię</th>
                                <th>Nazwisko</th>
                                <th>Email</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <a href="{{ route('admin.editUser', $user->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                            Edytuj
                                        </a>
                                        
                                        {{-- Sprawdzamy, czy to aktualnie zalogowany użytkownik --}}
                                        @if(auth()->user()->id !== $user->id)
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                                Usuń
                                            </button>

                                            {{-- Modal do potwierdzenia usunięcia --}}
                                            <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel{{ $user->id }}">Potwierdzenie usunięcia</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Czy na pewno chcesz usunąć użytkownika {{ $user->name }} {{ $user->last_name }}?</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                                                            <form action="{{ route('admin.deleteUser', $user->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Usuń</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted py-4">
                                        Brak użytkowników do wyświetlenia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 px-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
