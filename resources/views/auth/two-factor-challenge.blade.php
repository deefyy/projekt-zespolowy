<x-guest-layout>
    <div class="min-h-screen bg-[#f0f4f9] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <h2 class="text-center text-3xl font-bold text-[#002d62]">
                Weryfikacja dwuetapowa
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                To bezpieczny obszar aplikacji. Potwierdź kod lub użyj kodu zapasowego.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white py-10 px-10 shadow-xl rounded-2xl" x-data="{ showRecovery: false }">

                {{-- Formularz kodu uwierzytelniającego --}}
                <form method="POST" action="{{ route('two-factor.login') }}" x-show="!showRecovery">
                    @csrf

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Kod uwierzytelniający</label>
                        <input id="code" type="text" name="code" required autofocus
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#002d62] focus:ring-[#002d62]">
                        @error('code')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button"
                                class="text-sm text-blue-600 hover:underline"
                                x-on:click="showRecovery = true">
                            Użyj kodu zapasowego
                        </button>

                        <button type="submit"
                                class="bg-[#002d62] hover:bg-[#001b3e] text-white px-4 py-2 rounded-md shadow">
                            Potwierdź
                        </button>
                    </div>
                </form>

                {{-- Formularz kodu zapasowego --}}
                <form method="POST" action="{{ route('two-factor.login') }}" x-show="showRecovery" style="display: none;" class="mt-6">
                    @csrf

                    <div class="mb-2 text-sm text-gray-600">
                        Wprowadź kod zapasowy, aby się zalogować.
                    </div>

                    <div>
                        <label for="recovery_code" class="block text-sm font-medium text-gray-700">Kod zapasowy</label>
                        <input id="recovery_code" type="text" name="recovery_code" required autofocus
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#002d62] focus:ring-[#002d62]">
                        @error('recovery_code')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button"
                                class="text-sm text-blue-600 hover:underline"
                                x-on:click="showRecovery = false">
                            Użyj kodu uwierzytelniającego
                        </button>

                        <button type="submit"
                                class="bg-[#002d62] hover:bg-[#001b3e] text-white px-4 py-2 rounded-md shadow">
                            Potwierdź
                        </button>
                    </div>
                </form>

                {{-- Wiadomość statusu --}}
                @if (session('status'))
                    <div class="mt-6 font-medium text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Link do wyłączenia 2FA przez e-mail --}}
                <form method="POST" action="{{ route('two-factor.send-disable-link') }}" class="mt-6">
                    @csrf
                    <input type="hidden" name="email" value="{{ session('email') }}">
                    <button type="submit"
                            class="w-full text-center py-2 px-4 rounded-md bg-gray-200 hover:bg-gray-300 text-sm text-gray-700">
                        Wyślij e-mail z linkiem do wyłączenia 2FA
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
