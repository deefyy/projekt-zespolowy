<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

                        <!-- Two-Factor Authentication Section -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ __('Two-Factor Authentication') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Two-factor authentication adds an extra layer of security to your account.') }}
                    </p>
                    </br>
                </header>
                    @if (auth()->user()->two_factor_secret && session('auth.password_confirmed_at'))
                        <!-- Show QR Code -->
                        <div>
                            {!! auth()->user()->twoFactorQrCodeSvg() !!}
                        </div>

                        <!-- Show Recovery Codes -->
                        @if(!auth()->user()->two_factor_confirmed_at)
                        <div class="mt-4">
                            <h3 class="font-semibold text-lg">Recovery Codes:</h3>
                            <ul class="mt-2 bg-gray-100 p-3 rounded">
                                @foreach (auth()->user()->recoveryCodes() as $code)
                                    <li class="font-mono text-sm">{{ $code }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                        <div x-data="{ open: false }" class="mt-4">
                            <button @click="open = !open" class="flex items-center gap-2 text-gray-600 hover:underline">
                                <span x-text="open ? 'Hide Recovery Codes' : 'Show Recovery Codes'"></span>
                                <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2">
                                <h3 class="font-semibold text-lg">Recovery Codes:</h3>
                                <ul class="mt-2 bg-gray-100 p-3 rounded">
                                    @foreach (auth()->user()->recoveryCodes() as $code)
                                        <li class="font-mono text-sm">{{ $code }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                        <!-- Confirmation Status -->
                        @if (session('status') == 'two-factor-authentication-confirmed')
                            <div class="mt-4 p-3 bg-green-100 text-green-700 rounded">
                                {{__('Two-factor authentication confirmed and enabled successfully.')}}
                            </div>

                        @endif
                        @if ($errors->confirmTwoFactorAuthentication->has('code'))
                            <div class="mt-4 p-3 bg-red-100 text-red-700 rounded">
                                {{__('Two-factor authentication did not enable successfully.')}}
                            </div>
                        @endif
                        

                        <!-- Confirmation Form -->
                        @if (!auth()->user()->two_factor_confirmed_at)
                        <div class="mt-4">
                            <form method="POST" action="/user/confirmed-two-factor-authentication">
                                @csrf
                                <label for="code" class="block font-medium text-sm text-gray-700">Enter Authentication Code</label>
                                <input type="text" name="code" id="code" required
                                    class="mt-1 block w-full border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 rounded-md">
                                
                                @error('code')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                                <x-primary-button class="mt-2">{{ __('Confirm 2FA') }}</x-primary-button>
                            </form>
                        </div>
                        @endif
                        <!-- Disable 2FA Form -->
                        <form method="POST" action="/user/two-factor-authentication" class="mt-4">
                            @csrf
                            @method('delete')
                            <x-danger-button>{{ __('Disable') }}</x-danger-button>
                        </form>

                    @else
                        <!-- Enable 2FA Form -->
                        @if (session('auth.password_confirmed_at'))
                        
                        <form method="POST" action="/user/two-factor-authentication">
                            @csrf
                            <x-primary-button>{{ __('Enable') }}</x-primary-button>
                        </form>
                        @else
                        <p class="mt-1 mb-3 text-sm text-gray-600">
                        {{ __('Confirm your password to access this function.') }}
                        </p>
                        <form method="POST" action="/user/two-factor-authentication">
                            @csrf
                            <x-primary-button>{{ __('Confirm Password') }}</x-primary-button>
                        </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
