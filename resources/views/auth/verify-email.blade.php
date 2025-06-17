<x-guest-layout>
    <div class="min-h-screen bg-[#f0f4f9] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <h2 class="text-center text-3xl font-bold text-[#002d62]">
                Zweryfikuj swój adres e-mail
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Dziękujemy za rejestrację! Zanim rozpoczniesz korzystanie z aplikacji, potwierdź swój adres e-mail.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white py-10 px-10 shadow-xl rounded-2xl text-gray-700">

                {{-- Komunikat o wysłanym linku --}}
                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        Nowy link weryfikacyjny został wysłany na Twój adres e-mail.
                    </div>
                @endif

                <p class="mb-6 text-sm">
                    Kliknij w link weryfikacyjny, który wysłaliśmy na Twój adres e-mail. Jeśli nie otrzymałeś wiadomości, możesz wysłać nowy link.
                </p>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    {{-- Przycisk ponownego wysłania --}}
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit"
                                class="bg-[#002d62] hover:bg-[#001b3e] text-white px-5 py-2 rounded-md shadow">
                            Wyślij ponownie link weryfikacyjny
                        </button>
                    </form>

                    {{-- Przycisk wylogowania --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-sm text-gray-600 hover:underline focus:outline-none">
                            Wyloguj się
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
