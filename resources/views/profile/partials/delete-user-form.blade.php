<section class="space-y-6">
    <header class="mb-4">
        <h2 class="text-xl font-bold text-[#002d62]">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-2 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    {{-- Przycisk otwierający modal --}}
    <x-danger-button
        x-data
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl px-6 py-2"
    >
        {{ __('🗑️ Delete Account') }}
    </x-danger-button>

    {{-- Modal potwierdzający --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-white rounded-xl shadow space-y-6">
            @csrf
            @method('delete')

            <h2 class="text-xl font-bold text-[#002d62]">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="text-sm text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            {{-- Pole hasła --}}
            <div>
                <x-input-label for="password" value="{{ __('Password') }}" class="text-[#002d62]" />
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
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-xl">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
