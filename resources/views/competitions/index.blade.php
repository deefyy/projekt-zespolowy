<x-app-layout>
    <x-slot name="header">
        <div class="bg-[#002d62] text-white py-5">
            <h2 class="font-bold text-2xl text-center">Lista&nbsp;Konkursów</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- przycisk „Dodaj nowy” – tylko admin / organizator --}}
            @auth
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'organizator')
                    <a href="{{ route('competitions.create') }}"
                       class="bg-[#002d62] text-white px-5 py-3 rounded-lg inline-block hover:bg-[#001b3e] transition mb-6">
                        ➕ Dodaj nowy konkurs
                    </a>
                @endif
            @endauth

            @foreach ($competitions as $competition)
                <a href="{{ route('competitions.show', $competition) }}"
                   class="block bg-white shadow-md hover:shadow-xl hover:-translate-y-0.5 transition rounded-lg overflow-hidden">

                    {{-- ▶️ jeśli jest plakat – układ „obraz + treść”, inaczej tylko treść --}}
                    @if($competition->poster_path)
                        <div class="flex flex-col md:flex-row">
                            <div class="md:w-48 md:flex-shrink-0">
                                <img src="{{ Storage::url($competition->poster_path) }}"
                                     alt="Plakat {{ $competition->name }}"
                                     class="w-full h-48 md:h-full object-cover">
                            </div>

                            <div class="p-5 flex-1">
                                <h3 class="text-xl font-semibold text-[#002d62] mb-1 break-words">
                                    {{ $competition->name }}
                                </h3>
                                <p class="text-gray-600 mb-2 line-clamp-2 break-words">
                                    {{ $competition->description }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    📅 {{ $competition->start_date }} &nbsp;–&nbsp; {{ $competition->end_date }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="p-5">
                            <h3 class="text-xl font-semibold text-[#002d62] mb-1 break-words">
                                {{ $competition->name }}
                            </h3>
                            <p class="text-gray-600 mb-2 line-clamp-2 break-words">
                                {{ $competition->description }}
                            </p>
                            <p class="text-sm text-gray-500">
                                📅 {{ $competition->start_date }} &nbsp;–&nbsp; {{ $competition->end_date }}
                            </p>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
