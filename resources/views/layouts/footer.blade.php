<footer>
    {{-- ========== STYLE ========== --}}
    <style>
        /*  Tailwind – granatowa barwa z pierwszej wersji  */
        @layer utilities {
            .bg-pgblue { background-color:#003C71; }
        }

        /*  Własne drobne klasy  */
        .footer-link{
            color:#e5e7eb;                 /* zbliżone do text-gray-200 */
            text-decoration:none;
            transition:color .15s;
        }
        .footer-link:hover{ color:#ffffff; }

        .footer-social{
            color:#e5e7eb;
            transition:color .15s;
        }
        .footer-social:hover{ color:#ffffff; }

        /*  Grid XS – 2 kolumny; ≥ md przejmuje Bootstrap  */
        @media (max-width:767.98px){
            .footer-row{ display:grid; grid-template-columns:1fr 1fr; row-gap:1.5rem; column-gap:1rem; }
        }
    </style>

    {{-- ========== ZAWARTOŚĆ ========== --}}
    <div class="bg-pgblue text-gray-100 pt-8 pb-6 text-sm">
        <div class="container mx-auto px-4 lg:px-8">

            {{-- KOLUMNY --}}
            <div class="row footer-row gy-6">
                {{-- NAWIGACJA --}}
                <div class="col">
                    <h5 class="text-uppercase fw-bold mb-3">Nawigacja</h5>
                    <ul class="list-unstyled space-y-1">
                        <li><a class="footer-link" href="{{ route('home') }}">Strona główna</a></li>
                        <li><a class="footer-link" href="{{ route('forums.index') }}">Forum</a></li>
                        <li><a class="footer-link" href="{{ route('competitions.index') }}">Konkursy</a></li>
                    </ul>
                </div>

                {{-- KONKURSY --}}
                <div class="col">
                    <h5 class="text-uppercase fw-bold mb-3">Konkursy</h5>
                    <ul class="list-unstyled space-y-1">
                        <li><a class="footer-link" href="{{ route('competitions.index') }}">Wszystkie konkursy</a></li>
                        @auth
                            <li><a class="footer-link" href="{{ route('competitions.create') }}">Dodaj konkurs</a></li>
                        @endauth
                    </ul>
                </div>

                {{-- UŻYTKOWNIK --}}
                <div class="col">
                    <h5 class="text-uppercase fw-bold mb-3">Użytkownik</h5>
                    <ul class="list-unstyled space-y-1">
                        @auth
                            <li><a class="footer-link" href="{{ route('profile.edit') }}">Mój profil</a></li>
                            <li><a class="footer-link" href="{{ route('dashboard') }}">Kokpit</a></li>
                        @else
                            <li><a class="footer-link" href="{{ route('login') }}">Logowanie</a></li>
                            <li><a class="footer-link" href="{{ route('register') }}">Rejestracja</a></li>
                        @endauth
                    </ul>
                </div>

                {{-- KONTAKT + SOCIAL --}}
                <div class="col">
                    <h5 class="text-uppercase fw-bold mb-3">Kontakt</h5>
                    <address class="not-italic leading-relaxed mb-3">
                        Example&nbsp;Organization<br>
                        123&nbsp;Example&nbsp;Street<br>
                        00-000&nbsp;Example&nbsp;City<br>
                        tel.&nbsp;+48&nbsp;123&nbsp;456&nbsp;789<br>
                        <a class="footer-link" href="mailto:support@example.com">support@example.com</a>
                    </address>

                    <div class="d-flex gap-3">
                        <a aria-label="Facebook"  class="footer-social" href="#"><i class="bi bi-facebook fs-5"></i></a>
                        <a aria-label="Instagram" class="footer-social" href="#"><i class="bi bi-instagram fs-5"></i></a>
                        <a aria-label="LinkedIn"   class="footer-social" href="#"><i class="bi bi-linkedin fs-5"></i></a>
                        <a aria-label="YouTube"    class="footer-social" href="#"><i class="bi bi-youtube fs-5"></i></a>
                    </div>
                </div>
            </div>

            {{-- LINIA I DOLNY PASEK --}}
            <hr class="border-gray-200/50 my-6">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <p class="mb-0">&copy; {{ date('Y') }} Example&nbsp;Organization. Wszelkie prawa zastrzeżone.</p>
                <div class="d-flex gap-3">
                    <a class="footer-link" href="#">Polityka prywatności</a>
                    <a class="footer-link" href="#">Regulamin</a>
                </div>
            </div>
        </div>
    </div>
</footer>
