<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-[#002d62] m-0">{{ __('Add user') }}</h1>
            </div>
        </header>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.storeUser') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold text-[#002d62]">{{ __('First name') }}</label>
                                <input type="text" name="name" id="name" class="form-control border border-[#002d62]" required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label fw-semibold text-[#002d62]">{{ __('Last name') }}</label>
                                <input type="text" name="last_name" id="last_name" class="form-control border border-[#002d62]">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold text-[#002d62]">{{ __('Email') }}</label>
                                <input type="email" name="email" id="email" class="form-control border border-[#002d62]" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold text-[#002d62]">{{ __('Password') }}</label>
                                <input type="password" name="password" id="password" class="form-control border border-[#002d62]" required>
                            </div>

                            <div class="mb-4">
                                <label for="role" class="form-label fw-semibold text-[#002d62]">{{ __('Role') }}</label>
                                <select name="role" id="role" class="form-select border border-[#002d62]" required>
                                    <option value="user">{{ __('User') }}</option>
                                    <option value="admin">{{ __('Administrator') }}</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> {{ __('Back') }}
                                </a>
                                <button type="submit" class="btn" style="background-color: #002d62; color: white;">
                                    <i class="bi bi-save"></i> {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>