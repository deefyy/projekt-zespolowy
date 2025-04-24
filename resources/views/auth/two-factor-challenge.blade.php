<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your Code before continuing.') }}
    </div>

    <div x-data="{ showRecovery: false }"> 
        <!-- Standard 2FA Code Form -->
        <form method="POST" action="{{ route('two-factor.login') }}" x-show="!showRecovery">
            @csrf

            <!-- Code -->
            <div>
                <x-input-label for="code" :value="__('Code')" />
                <x-text-input id="code" type="text" name="code" required autofocus />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="flex justify-between mt-4">
                <button type="button" 
                    class="text-sm text-gray-600 hover:text-gray-900 underline focus:outline-none"
                    x-on:click="showRecovery = true">
                    {{ __('Use recovery code?') }}
                </button>

                <x-primary-button>
                    {{ __('Submit') }}
                </x-primary-button>
            </div>
        </form>

        <!-- Recovery Code Form - Initially Hidden -->
        <form method="POST" action="{{ route('two-factor.login') }}" x-show="showRecovery" style="display: none;">
            @csrf

            <div class="mb-4 text-sm text-gray-600">
                {{ __('Enter your recovery code to log in.') }}
            </div>

            <!-- Recovery Code -->
            <div>
                <x-input-label for="recovery_code" :value="__('Recovery Code')" />
                <x-text-input id="recovery_code" type="text" name="recovery_code" required autofocus />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            </div>

            <div class="flex justify-between mt-4">
                <button type="button" 
                    class="text-sm text-gray-600 hover:text-gray-900 underline focus:outline-none"
                    x-on:click="showRecovery = false">
                    {{ __('Use authentication code?') }}
                </button>

                <x-primary-button>
                    {{ __('Submit') }}
                </x-primary-button>
            </div>
        </form>
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif
        <form method="POST" action="{{ route('two-factor.send-disable-link') }}" class="mt-4">
            @csrf

            <input type="hidden" name="email" value="{{session('email')}}" />

            <x-primary-button>
                {{ __('Send email to disable 2FA') }}
            </x-primary-button>
        </form>

    </div>
</x-guest-layout>
