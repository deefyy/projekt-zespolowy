<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-center text-muted">
            {{ __('Dodaj użytkownika') }}
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.storeUser') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Imię</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Nazwisko</label>
                                <input type="text" name="last_name" id="last_name" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Hasło</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Rola</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="user">Użytkownik</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Wróć
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Zapisz
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
