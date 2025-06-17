<x-guest-layout>
    <div class="min-h-screen bg-[#f0f4f9] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <h2 class="text-center text-3xl font-bold text-[#002d62]">
                {{ __('Two-Factor Authentication') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('This is a secure area of the application. Please confirm your code or use a recovery code.') }}
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white py-10 px-10 shadow-xl rounded-2xl" x-data="{ showRecovery: false }">

                {{-- Formularz kodu uwierzytelniającego --}}
                <form method="POST" action="{{ route('two-factor.login') }}" x-show="!showRecovery">
                    @csrf

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">{{ __('Authentication Code') }}</label>
                        <input id="code" type="text" name="code" required autofocus
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#002d62] focus:ring-[#002d62]">
                        @error('code')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between items-center mt-6">
                        <button type="button"
                                class="text-sm text-blue-600 hover:underline"
                                x-on:click="showRecovery = true">
                            {{ __('Use a recovery code') }}
                        </button>

                        <button type="submit"
                                class="bg-[#002d62] hover:bg-[#001b3e] text-white px-4 py-2 rounded-md shadow">
                            {{ __('Confirm') }}
                        </button>
                    </div>
                </form>

                {{-- Formularz kodu zapasowego --}}
                <form method="POST" action="{{ route('two-factor.login') }}" x-show="showRecovery" style="display: none;">
                    @csrf

                    <div class="mb-2 text-sm text-gray-600">
                        {{ __('Enter a recovery code to log in.') }}
                    </div>

                    <div>
                        <label for="recovery_code" class="block text-sm font-medium text-gray-700">{{ __('Recovery Code') }}</label>
                        <input id="recovery_code" type="text" name="recovery_code" required autofocus
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#002d62] focus:ring-[#002d62]">
                        @error('recovery_code')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between items-center mt-6">
                        <button type="button"
                                class="text-sm text-blue-600 hover:underline"
                                x-on:click="showRecovery = false">
                            {{ __('Use an authentication code') }}
                        </button>

                        <button type="submit"
                                class="bg-[#002d62] hover:bg-[#001b3e] text-white px-4 py-2 rounded-md shadow">
                            {{ __('Log In') }}
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
                        {{ __('Send an email with a link to disable 2FA') }}
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-guest-layout>