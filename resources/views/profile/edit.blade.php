<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[#002d62] text-center">
                    Profil użytkownika
                </h2>
            </div>
        </header>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Aktualizacja danych użytkownika --}}
            <div class="bg-white border border-[#cdd7e4] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xl font-bold text-[#002d62] mb-4">Dane podstawowe</h3>
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Zmiana hasła --}}
            <div class="bg-white border border-[#cdd7e4] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xl font-bold text-[#002d62] mb-4">Zmiana hasła</h3>
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Usunięcie konta --}}
            <div class="bg-white border border-[#cdd7e4] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xl font-bold text-[#002d62] mb-4">Usuń konto</h3>
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            {{-- 2FA --}}
            <div class="bg-white border border-[#cdd7e4] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xl font-bold text-[#002d62] mb-4">Uwierzytelnianie dwuskładnikowe (2FA)</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Uwierzytelnianie dwuskładnikowe zapewnia dodatkową warstwę bezpieczeństwa Twojego konta.
                </p>

                @if (auth()->user()->two_factor_secret && session('auth.password_confirmed_at'))
                    {{-- QR code --}}
                    <div class="mb-4">{!! auth()->user()->twoFactorQrCodeSvg() !!}</div>

                    {{-- Recovery codes --}}
                    @if (!auth()->user()->two_factor_confirmed_at)
                        <div class="mb-4">
                            <h4 class="font-semibold text-md">Kody odzyskiwania:</h4>
                            <ul class="mt-2 bg-gray-100 p-3 rounded">
                                @foreach (auth()->user()->recoveryCodes() as $code)
                                    <li class="font-mono text-sm">{{ $code }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div x-data="{ open: false }" class="mb-4">
                            <button @click="open = !open" class="text-blue-600 hover:underline flex items-center gap-1">
                                <span x-text="open ? 'Ukryj kody odzyskiwania' : 'Pokaż kody odzyskiwania'"></span>
                                <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2">
                                <ul class="bg-gray-100 p-3 rounded">
                                    @foreach (auth()->user()->recoveryCodes() as $code)
                                        <li class="font-mono text-sm">{{ $code }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Status --}}
                    @if (session('status') == 'two-factor-authentication-confirmed')
                        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                            Uwierzytelnianie dwuskładnikowe zostało włączone.
                        </div>
                    @endif

                    {{-- Błędy --}}
                    @if ($errors->confirmTwoFactorAuthentication->has('code'))
                        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                            Nie udało się potwierdzić 2FA.
                        </div>
                    @endif

                    {{-- Potwierdzenie --}}
                    @if (!auth()->user()->two_factor_confirmed_at)
                        <form method="POST" action="/user/confirmed-two-factor-authentication" class="mb-4">
                            @csrf
                            <label for="code" class="block font-medium text-sm text-gray-700">Kod autoryzacyjny</label>
                            <input type="text" name="code" id="code" required
                                   class="mt-1 block w-full border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 rounded-md">
                            @error('code')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror

                            <x-primary-button class="mt-2">{{ __('Potwierdź 2FA') }}</x-primary-button>
                        </form>
                    @endif

                    {{-- Wyłączenie --}}
                    <form method="POST" action="/user/two-factor-authentication">
                        @csrf
                        @method('delete')
                        <x-danger-button>{{ __('Wyłącz 2FA') }}</x-danger-button>
                    </form>

                @else
                    {{-- Włączenie --}}
                    @if (session('auth.password_confirmed_at'))
                        <form method="POST" action="/user/two-factor-authentication">
                            @csrf
                            <x-primary-button>{{ __('Włącz 2FA') }}</x-primary-button>
                        </form>
                    @else
                        <p class="text-sm text-gray-600 mb-2">Potwierdź hasło, aby uzyskać dostęp do tej funkcji.</p>
                        <form method="POST" action="/user/two-factor-authentication">
                            @csrf
                            <x-primary-button>{{ __('Potwierdź hasło') }}</x-primary-button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
