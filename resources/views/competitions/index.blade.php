<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Lista konkursów
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Tylko dla admina --}}
            @if(auth()->user() && auth()->user()->role === 'admin')
                <a href="{{ route('competitions.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
                    Dodaj nowy konkurs
                </a>
            @endif

            @foreach ($competitions as $competition)
                <a href="{{ route('competitions.show', $competition) }}" class="block bg-white shadow-sm rounded-lg p-4 mb-4 hover:bg-gray-50 transition">
                    <h3 class="text-lg font-bold">{{ $competition->name }}</h3>
                    <p class="text-gray-700">{{ $competition->description }}</p>
                    <p class="text-sm text-gray-500">
                        Od: {{ $competition->start_date }} do: {{ $competition->end_date }}
                    </p>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
