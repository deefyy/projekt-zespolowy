<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-[#002d62] m-0">Edytuj użytkownika</h1>
            </div>
        </header>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form id="editUserForm" method="POST" action="{{ route('admin.updateUser', $user->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold text-[#002d62]">Imię</label>
                                <input type="text" name="name" id="name" class="form-control border border-[#002d62]" value="{{ $user->name }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label fw-semibold text-[#002d62]">Nazwisko</label>
                                <input type="text" name="last_name" id="last_name" class="form-control border border-[#002d62]" value="{{ $user->last_name }}">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold text-[#002d62]">Email</label>
                                <input type="email" name="email" id="email" class="form-control border border-[#002d62]" value="{{ $user->email }}" required>
                            </div>

                            <div class="mb-4">
                                <label for="role" class="form-label fw-semibold text-[#002d62]">Rola</label>
                                <select name="role" id="role" class="form-select border border-[#002d62]" required>
                                    <option value="user" {{ old('role', $user->role ?? '') == 'user' ? 'selected' : '' }}>Użytkownik</option>
                                    <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Administrator</option>
                                    <option value="organizator" {{ old('role', $user->role ?? '') == 'organizator' ? 'selected' : '' }}>Organizator</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Wróć
                                </a>
                                <button type="button" class="btn" style="background-color: #002d62; color: white;" data-bs-toggle="modal" data-bs-target="#confirmationModal">
                                    <i class="bi bi-save"></i> Zapisz zmiany
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal potwierdzający --}}
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title text-[#002d62]" id="confirmationModalLabel">Potwierdzenie edycji</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Czy na pewno chcesz zaktualizować dane tego użytkownika?</p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" form="editUserForm" class="btn" style="background-color: #002d62; color: white;">Potwierdź</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
