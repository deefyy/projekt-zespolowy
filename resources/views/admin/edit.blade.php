<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-[#002d62] text-center">{{ __('Edit user') }}</h1>
            </div>
        </header>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen">
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-md border border-[#cdd7e4]">
            <form id="editUserForm" method="POST" action="{{ route('admin.updateUser', $user->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block font-semibold text-[#002d62] mb-1">{{ __('Name') }}</label>
                    <input type="text" name="name" id="name" class="w-full border border-[#002d62] rounded-xl px-4 py-2" value="{{ $user->name }}" required>
                </div>

                <div>
                    <label for="last_name" class="block font-semibold text-[#002d62] mb-1">{{ __('Surname') }}</label>
                    <input type="text" name="last_name" id="last_name" class="w-full border border-[#002d62] rounded-xl px-4 py-2" value="{{ $user->last_name }}">
                </div>

                <div>
                    <label for="email" class="block font-semibold text-[#002d62] mb-1">{{ __('Email') }}</label>
                    <input type="email" name="email" id="email" class="w-full border border-[#002d62] rounded-xl px-4 py-2" value="{{ $user->email }}" required>
                </div>

                <div>
                    <label for="role" class="block font-semibold text-[#002d62] mb-1">{{ __('Role') }}</label>
                    <select name="role" id="role" class="w-full border border-[#002d62] rounded-xl px-4 py-2" required>
                        <option value="user" {{ old('role', $user->role ?? '') == 'user' ? 'selected' : '' }}>{{ __('User') }}</option>
                        <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>{{ __('Administrator') }}</option>
                        <option value="organizator" {{ old('role', $user->role ?? '') == 'organizator' ? 'selected' : '' }}>{{ __('Organizer') }}</option>
                    </select>
                </div>

                <div class="flex justify-between mt-6">
                    <a href="{{ route('admin.dashboard') }}" class="inline-block px-6 py-2 border rounded-xl text-[#002d62] border-[#002d62] hover:bg-[#eaf0f6] transition">← {{ __('Back') }}</a>

                    <button type="button" class="px-6 py-2 bg-[#002d62] text-white rounded-xl hover:bg-[#001b3c] transition" onclick="document.getElementById('confirmModal').classList.remove('hidden')">
                        💾 {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal potwierdzający --}}
    <div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 border border-gray-300">
            <h2 class="text-lg font-bold text-[#002d62] mb-4">{{ __('Edit confirmation') }}</h2>
            <p class="text-gray-700 mb-6">{{ __('Are you sure you want to update this user\'s data?') }}</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('confirmModal').classList.add('hidden')" class="px-4 py-2 border rounded hover:bg-gray-100">{{ __('Cancel') }}</button>
                <button type="submit" form="editUserForm" class="px-4 py-2 bg-[#002d62] text-white rounded hover:bg-[#001b3c]">{{ __('Confirm') }}</button>
            </div>
        </div>
    </div>
</x-app-layout>
