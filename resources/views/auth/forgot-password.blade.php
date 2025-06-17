<x-guest-layout>
    <div class="min-h-screen bg-[#f0f4f9] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <h2 class="text-center text-3xl font-bold text-[#002d62]">
                Resetowanie hasła
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Wpisz swój adres e-mail, a wyślemy Ci link do zresetowania hasła.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white py-10 px-10 shadow-xl rounded-2xl">

                {{-- Komunikat sesji --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
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

                    {{-- Przycisk wysyłania linku --}}
                    <div>
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-white bg-[#002d62] hover:bg-[#001b3e] transition">
                            Wyślij link resetujący hasło
                        </button>
                    </div>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600">
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">← Wróć do logowania</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
