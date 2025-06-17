<section>
    <header class="mb-6">
        <h2 class="text-xl font-bold text-[#002d62]">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-2 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        {{-- Imię --}}
        <div>
            <x-input-label for="name" value="{{ __('Name') }}" class="text-[#002d62]" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full border border-[#cdd7e4] rounded-xl shadow-sm"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- Nazwisko --}}
        <div>
            <x-input-label for="last_name" value="{{ __('Surname') }}" class="text-[#002d62]" />
            <x-text-input
                id="last_name"
                name="last_name"
                type="text"
                class="mt-1 block w-full border border-[#cdd7e4] rounded-xl shadow-sm"
                :value="old('last_name', $user->last_name)"
                required
                autocomplete="family-name" />
            <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" value="{{ __('Email Address') }}" class="text-[#002d62]" />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full border border-[#cdd7e4] rounded-xl shadow-sm"
                :value="old('email', $user->email)"
                required
                autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            {{-- Weryfikacja e-maila --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 text-sm text-gray-700">
                    <p>
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification"
                            class="underline text-sm text-blue-700 hover:text-blue-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#002d62]">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Przycisk Zapisz --}}
        <div class="flex items-center gap-4">
            <x-primary-button class="bg-[#002d62] hover:bg-[#001b3e] rounded-xl px-6 py-2">
                {{ __('Save changes') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-700"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
