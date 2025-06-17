<nav x-data="{ open: false }" class="bg-[#002d62] text-white shadow-sm border-b border-[#001b3c]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}">
                    <x-application-logo class="block h-10 w-auto" />
                </a>
                <div class="hidden sm:flex space-x-6 text-lg font-semibold tracking-wide">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-white hover:text-blue-300">
                        {{ __('Home') }}
                    </x-nav-link>
                    <x-nav-link :href="route('competitions.index')" :active="request()->routeIs('competitions.*')" class="text-white hover:text-blue-300">
                        {{ __('Competitions') }}
                    </x-nav-link>
                    <x-nav-link :href="route('forums.index')" :active="request()->routeIs('forums.*')" class="text-white hover:text-blue-300">
                        {{ __('Forum') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-4">
                <div class="flex items-center gap-2 text-white text-base font-semibold">
                    <a href="{{ route('language.set', 'pl') }}" class="{{ session('locale', config('app.locale')) == 'pl' ? 'underline' : 'opacity-75' }} hover:opacity-100">PL</a>
                    <span>/</span>
                    <a href="{{ route('language.set', 'en') }}" class="{{ session('locale', config('app.locale')) == 'en' ? 'underline' : 'opacity-75' }} hover:opacity-100">EN</a>
                </div>
                
                {{-- WCAG IKONY --}}
                <div class="flex items-center gap-2 text-white text-base">
                    <button onclick="resetWCAGSettings()" class="px-2 py-1 hover:bg-white hover:text-[#002d62] rounded transition border border-transparent hover:border-white">A</button>
                    <button onclick="adjustFontSize('increase')" class="px-2 py-1 hover:bg-white hover:text-[#002d62] rounded transition border border-transparent hover:border-white">A+</button>
                    <button onclick="adjustFontSize('decrease')" class="px-2 py-1 hover:bg-white hover:text-[#002d62] rounded transition border border-transparent hover:border-white">A-</button>
                    <button onclick="toggleContrast()" class="px-2 py-1 bg-black text-yellow-400 rounded border-2 border-white font-bold">A</button>
                </div>

                {{-- Autoryzacja --}}
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="px-3 py-2 border border-white text-sm rounded hover:bg-white hover:text-[#002d62] transition flex items-center gap-2">
                                {{ Auth::user()->name }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('My profile') }}</x-dropdown-link>
                            @if (Auth::user()->role === 'admin')
                                <x-dropdown-link :href="route('admin.dashboard')">{{ __('Admin Panel') }}</x-dropdown-link>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex gap-3">
                        <a href="{{ route('login') }}" class="font-semibold hover:text-blue-300">{{ __('Log In') }}</a>
                        <a href="{{ route('register') }}" class="font-semibold hover:text-blue-300">{{ __('Register') }}</a>
                    </div>
                @endauth
            </div>

            {{-- Mobile toggle --}}
            <div class="sm:hidden">
                <button @click="open = !open" class="p-2 rounded-md text-white hover:bg-[#001b3c] transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" d="M4 6h16M4 12h16M4 18h16" stroke-width="2" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" d="M6 18L18 6M6 6l12 12" stroke-width="2" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden bg-[#002d62] px-4 py-3 space-y-1 text-sm">
        <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-white">{{ __('Home') }}</x-responsive-nav-link>
        <x-responsive-nav-link :href="route('competitions.index')" :active="request()->routeIs('competitions.*')" class="text-white">{{ __('Competitions') }}</x-responsive-nav-link>
        <x-responsive-nav-link :href="route('forums.index')" :active="request()->routeIs('forums.*')" class="text-white">{{ __('Forum') }}</x-responsive-nav-link>
    </div>
</nav>