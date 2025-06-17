<x-guest-layout>
    <div class="min-h-screen bg-[#f0f4f9] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <h2 class="text-center text-3xl font-bold text-[#002d62]">
                {{ __('Verify Your Email Address') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('Thanks for signing up! Before getting started, please confirm your email address.') }}
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white py-10 px-10 shadow-xl rounded-2xl text-gray-700">

                {{-- Komunikat o wysłanym linku --}}
                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </div>
                @endif

                <p class="mb-6 text-sm">
                    {{ __("Click the verification link we sent to your email address. If you didn't receive the email, you can send a new link.") }}
                </p>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    {{-- Przycisk ponownego wysłania --}}
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit"
                                class="bg-[#002d62] hover:bg-[#001b3e] text-white px-5 py-2 rounded-md shadow">
                            {{ __('Resend Verification Link') }}
                        </button>
                    </form>

                    {{-- Przycisk wylogowania --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-sm text-gray-600 hover:underline focus:outline-none">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
