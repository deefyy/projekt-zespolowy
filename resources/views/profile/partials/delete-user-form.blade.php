<section class="space-y-6">
    <header class="mb-4">
        <h2 class="text-xl font-bold text-[#002d62]">
            {{ __('Usuń konto') }}
        </h2>

        <p class="mt-2 text-sm text-gray-600">
            {{ __('Po usunięciu konta wszystkie dane zostaną trwale usunięte. Przed kontynuacją pobierz wszelkie potrzebne informacje.') }}
        </p>
    </header>

    {{-- Przycisk otwierający modal --}}
    <x-danger-button
        x-data
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl px-6 py-2"
    >
        {{ __('🗑️ Usuń konto') }}
    </x-danger-button>

    {{-- Modal potwierdzający --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-white rounded-xl shadow space-y-6">
            @csrf
            @method('delete')

            <h2 class="text-xl font-bold text-[#002d62]">
                {{ __('Czy na pewno chcesz usunąć konto?') }}
            </h2>

            <p class="text-sm text-gray-600">
                {{ __('Po potwierdzeniu wszystkie dane zostaną trwale usunięte. Wpisz swoje hasło, aby potwierdzić.') }}
            </p>

            {{-- Pole hasła --}}
            <div>
                <x-input-label for="password" :value="__('Hasło')" class="text-[#002d62]" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="••••••••"
                    class="mt-1 block w-full border border-[#cdd7e4] rounded-xl shadow-sm"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            {{-- Przyciski --}}
            <div class="flex justify-end gap-3">
                <x-secondary-button
                    x-on:click="$dispatch('close')"
                    class="border border-[#cdd7e4] text-[#002d62] hover:bg-gray-100 px-5 py-2 rounded-xl"
                >
                    {{ __('Anuluj') }}
                </x-secondary-button>

                <x-danger-button class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-xl">
                    {{ __('Usuń konto') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
