<x-app-layout>
    <x-slot name="header">
        <div class="bg-[#002d62] text-white py-5">
            <h2 class="font-bold text-2xl text-center">
                Lista Konkursów
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Tylko dla admina --}}
            @if(auth()->user() && (auth()->user()->role === 'admin' || auth()->user()->role === 'organizator'))
                <a href="{{ route('competitions.create') }}" class="bg-[#002d62] text-white px-5 py-3 rounded-lg mb-6 inline-block hover:bg-[#001b3e] transition">
                    ➕ Dodaj nowy konkurs
                </a>
            @endif

            @foreach ($competitions as $competition)
                <a href="{{ route('competitions.show', $competition) }}" class="block bg-white shadow-lg rounded-lg p-5 mb-4 hover:shadow-xl transition">
                    <h3 class="text-xl font-bold text-[#002d62] mb-2 break-words">{{ $competition->name }}</h3>
                    <p class="text-gray-700 mb-3 break-words">{{ $competition->description }}</p>
                    <p class="text-sm text-gray-500 mb-1">
                        📅 Od: {{ $competition->start_date }} do: {{ $competition->end_date }}
                    </p>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
