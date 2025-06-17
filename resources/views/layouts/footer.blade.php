<footer class="bg-[#003c71] text-gray-200 text-sm pt-10 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <h4 class="uppercase font-semibold tracking-wide mb-3">Nawigacja</h4>
                <ul class="space-y-1">
                    <li><a href="{{ route('home') }}" class="hover:text-white">Strona główna</a></li>
                    <li><a href="{{ route('competitions.index') }}" class="hover:text-white">Konkursy</a></li>
                    <li><a href="{{ route('forums.index') }}" class="hover:text-white">Forum</a></li>
                </ul>
            </div>
            <div>
                <h4 class="uppercase font-semibold tracking-wide mb-3">Konto</h4>
                <ul class="space-y-1">
                    @auth
                        <li><a href="{{ route('profile.edit') }}" class="hover:text-white">Mój profil</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-white">Zaloguj się</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white">Rejestracja</a></li>
                    @endauth
                </ul>
            </div>
            
            <div>
                <h4 class="uppercase font-semibold tracking-wide mb-3">Kontakt</h4>
                <address class="not-italic leading-relaxed text-sm">
                    Test<br>
                    ul. Test 2, 00-000 test<br>
                    <a href="mailto:support@example.com" class="hover:text-white">support@example.com</a>
                </address>
                <div class="flex space-x-3 mt-3 text-lg">
                    <a href="#" class="hover:text-white"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="hover:text-white"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="hover:text-white"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="hover:text-white"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
        </div>

        <hr class="border-gray-500 my-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-2">
            <p class="mb-0">&copy; {{ date('Y') }} Wszelkie prawa zastrzeżone.</p>
            <div class="flex space-x-4">
                <a href="#" class="hover:text-white">Polityka prywatności</a>
                <a href="#" class="hover:text-white">Regulamin</a>
            </div>
        </div>
    </div>
</footer>
