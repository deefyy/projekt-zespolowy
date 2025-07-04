<x-guest-layout>
    <div class="min-h-screen bg-[#f0f4f9] py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white py-12 px-10 shadow-xl rounded-2xl">
                <h2 class="text-center text-4xl font-bold text-[#002d62]">
                    {{ __('Register an account') }}
                </h2>
                <p class="mt-2 text-center text-base text-gray-600">
                    {{ __('Fill out the form to create an account in the competition system') }}
                </p>

                <form method="POST" action="{{ route('register') }}" class="mt-10 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- Imię --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('First name') }}</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#002d62] focus:border-[#002d62]">
                            @error('name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nazwisko --}}
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">{{ __('Last name') }}</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#002d62] focus:border-[#002d62]">
                            @error('last_name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email Address') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#002d62] focus:border-[#002d62]">
                        @error('email')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Hasło --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                            <input id="password" name="password" type="password" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#002d62] focus:border-[#002d62]">
                            @error('password')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirm Password') }}</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#002d62] focus:border-[#002d62]">
                            @error('password_confirmation')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Informacja o RODO --}}
                    <div class="bg-gray-100 text-sm text-gray-600 rounded-lg p-4 leading-snug">
                        {{ __('By registering, you agree to the processing of personal data for purposes related to the organization of competitions, in accordance with the') }} <a href="#" class="text-blue-600 hover:underline">{{ __('GDPR policy') }}</a>.
                    </div>

                    {{-- Przyciski --}}
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">
                            {{ __('Already have an account? Log in') }}
                        </a>

                        <button type="submit"
                            class="w-full sm:w-auto bg-[#002d62] text-white px-8 py-2 rounded-lg hover:bg-[#001b3c] transition">
                            {{ __('Register') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
