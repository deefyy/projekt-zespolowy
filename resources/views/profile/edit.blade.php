<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[#002d62] text-center">
                    {{ __('User Profile') }}
                </h2>
            </div>
        </header>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white border border-[#cdd7e4] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xl font-bold text-[#002d62] mb-4">{{ __('Basic Information') }}</h3>
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-white border border-[#cdd7e4] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xl font-bold text-[#002d62] mb-4">{{ __('Change Password') }}</h3>
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="bg-white border border-[#cdd7e4] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xl font-bold text-[#002d62] mb-4">{{ __('Delete Account') }}</h3>
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <div class="bg-white border border-[#cdd7e4] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xl font-bold text-[#002d62] mb-4">{{ __('Two-Factor Authentication (2FA)') }}</h3>
                <p class="text-sm text-gray-600 mb-4">
                    {{ __('Two-factor authentication provides an extra layer of security for your account.') }}
                </p>

                @if (auth()->user()->two_factor_secret && session('auth.password_confirmed_at'))
                    <div class="mb-4">{!! auth()->user()->twoFactorQrCodeSvg() !!}</div>

                    @if (!auth()->user()->two_factor_confirmed_at)
                        <div class="mb-4">
                            <h4 class="font-semibold text-md">{{ __('Recovery Codes:') }}</h4>
                            <ul class="mt-2 bg-gray-100 p-3 rounded">
                                @foreach (auth()->user()->recoveryCodes() as $code)
                                    <li class="font-mono text-sm">{{ $code }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div x-data="{ open: false }" class="mb-4">
                            <button @click="open = !open" class="text-blue-600 hover:underline flex items-center gap-1">
                                <span x-text="open ? '{{ __('Hide recovery codes') }}' : '{{ __('Show recovery codes') }}'"></span>
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

                    @if (session('status') == 'two-factor-authentication-confirmed')
                        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                            {{ __('Two-factor authentication has been enabled.') }}
                        </div>
                    @endif

                    @if ($errors->confirmTwoFactorAuthentication->has('code'))
                        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                            {{ __('Failed to confirm 2FA.') }}
                        </div>
                    @endif

                    @if (!auth()->user()->two_factor_confirmed_at)
                        <form method="POST" action="/user/confirmed-two-factor-authentication" class="mb-4">
                            @csrf
                            <label for="code" class="block font-medium text-sm text-gray-700">{{ __('Authorization Code') }}</label>
                            <input type="text" name="code" id="code" required
                                   class="mt-1 block w-full border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 rounded-md">
                            @error('code')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <x-primary-button class="mt-2">{{ __('Confirm 2FA') }}</x-primary-button>
                        </form>
                    @endif

                    <form method="POST" action="/user/two-factor-authentication">
                        @csrf
                        @method('delete')
                        <x-danger-button>{{ __('Disable 2FA') }}</x-danger-button>
                    </form>

                @else
                    @if (session('auth.password_confirmed_at'))
                        <form method="POST" action="/user/two-factor-authentication">
                            @csrf
                            <x-primary-button>{{ __('Enable 2FA') }}</x-primary-button>
                        </form>
                    @else
                        <p class="text-sm text-gray-600 mb-2">{{ __('Confirm your password to access this feature.') }}</p>
                        <form method="POST" action="{{ route('password.confirm') }}">
                            @csrf
                             <x-primary-button>{{ __('Confirm password') }}</x-primary-button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
