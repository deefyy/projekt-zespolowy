<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-[#002d62] text-center">{{ __('Add user') }}</h1>
            </div>
        </header>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-2xl p-8">
                <form method="POST" action="{{ route('admin.storeUser') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-semibold text-[#002d62]">{{ __('First name') }}</label>
                        <input type="text" name="name" id="name"
                               class="mt-1 block w-full rounded-xl border-[#cdd7e4] shadow-sm focus:border-[#002d62] focus:ring-[#002d62]"
                               required>
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-[#002d62]">{{ __('Last name') }}</label>
                        <input type="text" name="last_name" id="last_name"
                               class="mt-1 block w-full rounded-xl border-[#cdd7e4] shadow-sm focus:border-[#002d62] focus:ring-[#002d62]">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#002d62]">{{ __('Email') }}</label>
                        <input type="email" name="email" id="email"
                               class="mt-1 block w-full rounded-xl border-[#cdd7e4] shadow-sm focus:border-[#002d62] focus:ring-[#002d62]"
                               required>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#002d62]">{{ __('Password') }}</label>
                        <input type="password" name="password" id="password"
                               class="mt-1 block w-full rounded-xl border-[#cdd7e4] shadow-sm focus:border-[#002d62] focus:ring-[#002d62]"
                               required>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-semibold text-[#002d62]">{{ __('Role') }}</label>
                        <select name="role" id="role"
                                class="mt-1 block w-full rounded-xl border-[#cdd7e4] shadow-sm focus:border-[#002d62] focus:ring-[#002d62]"
                                required>
                            <option value="user">{{ __('User') }}</option>
                            <option value="admin">{{ __('Administrator') }}</option>
                        </select>
                    </div>

                    <div class="flex justify-between items-center">
                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-block bg-gray-300 text-gray-800 px-5 py-2 rounded-xl hover:bg-gray-400 transition">
                            ← Wróć
                        </a>
                        <button type="submit"
                                class="bg-[#002d62] text-white px-6 py-2 rounded-xl hover:bg-[#001b3e] transition">
                            💾 Zapisz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>