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
                    @if (auth()->user()->two_factor_secret)
                        <!-- Show QR Code -->
                        <div>
                            {!! auth()->user()->twoFactorQrCodeSvg() !!}
                        </div>

                        <!-- Show Recovery Codes -->
                        <div class="mt-4">
                                    <li class="font-mono text-sm">{{ $code }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Confirmation Status -->
                        @if (session('status') == 'two-factor-authentication-confirmed')
                            <div class="mt-4 p-3 bg-green-100 text-green-700 rounded">
                                Two-factor authentication confirmed and enabled successfully.
                            </div>
                        @endif

                        <!-- Confirmation Form -->
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

                        <!-- Disable 2FA Form -->
                        <form method="POST" action="/user/two-factor-authentication" class="mt-4">
                            @csrf
                            @method('delete')
                            <x-danger-button>{{ __('Disable') }}</x-danger-button>
                        </form>

                    @else
                        <!-- Enable 2FA Form -->
                        <form method="POST" action="/user/two-factor-authentication">
                            @csrf
                            <x-primary-button>{{ __('Enable') }}</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
