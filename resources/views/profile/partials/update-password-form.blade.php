<section>
    <header class="mb-6">
        <h2 class="text-xl font-bold text-[#002d62]">
            {{ __('Zmień hasło') }}
        </h2>

        <p class="mt-2 text-sm text-gray-600">
            {{ __('Upewnij się, że Twoje hasło jest długie i trudne do odgadnięcia.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        {{-- Obecne hasło --}}
        <div>
            <x-input-label for="update_password_current_password" :value="__('Obecne hasło')" class="text-[#002d62]" />
            <x-text-input
                id="update_password_current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                class="mt-1 block w-full border border-[#cdd7e4] rounded-xl shadow-sm"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        {{-- Nowe hasło --}}
        <div>
            <x-input-label for="update_password_password" :value="__('Nowe hasło')" class="text-[#002d62]" />
            <x-text-input
                id="update_password_password"
                name="password"
                type="password"
                autocomplete="new-password"
                class="mt-1 block w-full border border-[#cdd7e4] rounded-xl shadow-sm"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        {{-- Potwierdzenie nowego hasła --}}
        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Potwierdź hasło')" class="text-[#002d62]" />
            <x-text-input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                class="mt-1 block w-full border border-[#cdd7e4] rounded-xl shadow-sm"
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Zapisz --}}
        <div class="flex items-center gap-4">
            <x-primary-button class="bg-[#002d62] hover:bg-[#001b3e] rounded-xl px-6 py-2">
                {{ __('Zapisz') }}
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-700"
                >{{ __('Zapisano.') }}</p>
            @endif
        </div>
    </form>
</section>
