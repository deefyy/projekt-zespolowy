<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[#002d62] text-center">{{ __('Competition List') }}</h2>
            </div>
        </header>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FORMULARZ WYSZUKIWANIA --}}
            <form method="GET" action="{{ route('competitions.index') }}" class="flex flex-wrap gap-4 items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search for a competition...') }}"
                       class="border border-[#cdd7e4] rounded-xl px-4 py-2 w-full sm:w-80 shadow-sm">
                <button type="submit"
                        class="bg-[#002d62] text-white px-5 py-2 rounded-xl hover:bg-[#001b3e] transition">
                    🔍 {{ __('Search') }}
                </button>
            </form>

            {{-- PRZYCISK DODAWANIA NOWEGO KONKURSU --}}
            @auth
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'organizator')
                    <a href="{{ route('competitions.create') }}"
                       class="inline-block bg-[#002d62] text-white px-6 py-3 rounded-xl hover:bg-[#001b3e] transition font-semibold">
                        ➕ {{ __('Add new competition') }}
                    </a>
                @endif
            @endauth

            {{-- LISTA KONKURSÓW --}}
            @foreach ($competitions as $competition)
                <a href="{{ route('competitions.show', $competition) }}"
                   class="block bg-white border border-[#cdd7e4] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
                    <div class="flex flex-col md:flex-row">
                        @if($competition->poster_path)
                            <div class="md:w-64 w-full h-48 md:h-auto relative">
                                <img src="{{ Storage::url($competition->poster_path) }}"
                                     alt="{{ __('Poster for') }} {{ $competition->name }}"
                                     class="absolute inset-0 w-full h-full object-cover object-center md:rounded-l-2xl">
                            </div>
                        @endif
                        <div class="p-6 flex-1 min-w-0">
                            <h3 class="text-xl font-bold text-[#002d62] mb-2 break-words">
                                {{ $competition->name }}
                            </h3>
                            <p class="text-gray-700 text-sm mb-3 line-clamp-3">
                                {{ Str::limit(strip_tags($competition->description), 150, '...') }}
                            </p>
                            <p class="text-sm text-gray-500">
                                📅 {{ \Carbon\Carbon::parse($competition->start_date)->format('d.m.Y') }}
                                @if($competition->end_date && $competition->end_date != $competition->start_date)
                                    – {{ \Carbon\Carbon::parse($competition->end_date)->format('d.m.Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach

        </div>
    </div>
</x-app-layout>