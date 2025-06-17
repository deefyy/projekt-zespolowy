<x-guest-layout>
    <div class="min-h-screen bg-[#f0f4f9] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <h2 class="text-center text-3xl font-bold text-[#002d62]">
                Zaloguj się do konta
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Panel Konkursów
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white py-10 px-10 shadow-xl rounded-2xl">

                {{-- Komunikat sesji --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Adres e-mail</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#002d62] focus:ring-[#002d62]">
                        @error('email')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Hasło --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Hasło</label>
                        <input id="password" type="password" name="password" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#002d62] focus:ring-[#002d62]">
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me + Forgot --}}
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="flex items-center">
                            <input id="remember_me" type="checkbox"
                                   class="h-4 w-4 text-[#002d62] border-gray-300 rounded focus:ring-[#002d62]" name="remember">
                            <span class="ml-2 text-sm text-gray-700">Zapamiętaj mnie</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-blue-600 hover:underline"
                               href="{{ route('password.request') }}">
                                Nie pamiętasz hasła?
                            </a>
                        @endif
                    </div>

                    {{-- Przycisk logowania --}}
                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-white bg-[#002d62] hover:bg-[#001b3e] transition">
                            Zaloguj
                        </button>
                    </div>
                </form>

                {{-- Link do rejestracji --}}
                <p class="mt-6 text-center text-sm text-gray-600">
                    Nie masz konta?
                    <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Zarejestruj się</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
