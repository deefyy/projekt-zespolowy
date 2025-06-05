<x-app-layout>
    {{-- HEADER -------------------------------------------------------}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Strona główna
        </h2>
    </x-slot>

    {{-- PAGE ---------------------------------------------------------}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Najbliższe wydarzenia ----------------------------------}}
            <section>
                <h3 class="text-3xl font-bold text-blue-900 mb-6">Najbliższe wydarzenia</h3>

                @forelse($upcomingCompetitions as $competition)
                    <article class="bg-white rounded-lg shadow hover:shadow-lg transition mb-6 overflow-hidden">

                        @if($competition->poster_path)
                            {{-- FLEX ▸ treść | obraz (desktop) --}}
                            <div class="flex flex-col md:flex-row">

                                {{-- TREŚĆ --------------------------------------------------}}
                                <div class="p-6 flex-1 min-w-0">
                                    <h4 class="text-2xl font-semibold text-blue-900 mb-1 break-words">
                                        {{ $competition->name }}
                                    </h4>

                                    <p class="text-gray-600 text-sm mb-2">
                                        📅 {{ \Carbon\Carbon::parse($competition->start_date)->format('d.m.Y') }}
                                        @if($competition->end_date)
                                            – {{ \Carbon\Carbon::parse($competition->end_date)->format('d.m.Y') }}
                                        @endif
                                    </p>

                                    <p class="text-gray-800 mb-4 line-clamp-3 break-words">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($competition->description), 120, '…') }}
                                    </p>

                                    <a href="{{ route('competitions.show', $competition) }}"
                                       class="inline-block bg-blue-800 hover:bg-blue-900 text-white text-sm font-medium py-2 px-4 rounded">
                                        Czytaj dalej…
                                    </a>
                                </div>

                                {{-- MINIATURA 16 : 9  (po PRAWEJ) --}}
                                <div class="md:w-64 flex-shrink-0">
                                    <div class="relative w-full pb-[56.25%]">
                                        <img src="{{ Storage::url($competition->poster_path) }}"
                                             alt="Plakat {{ $competition->name }}"
                                             class="absolute inset-0 w-full h-full object-cover">
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- bez plakatu – tylko treść --}}
                            <div class="p-6">
                                <h4 class="text-2xl font-semibold text-blue-900 break-words mb-1">
                                    {{ $competition->name }}
                                </h4>

                                <p class="text-gray-600 text-sm mb-2">
                                    📅 {{ \Carbon\Carbon::parse($competition->start_date)->format('d.m.Y') }}
                                    @if($competition->end_date)
                                        – {{ \Carbon\Carbon::parse($competition->end_date)->format('d.m.Y') }}
                                    @endif
                                </p>

                                <p class="text-gray-800 mb-4 line-clamp-3">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($competition->description), 120, '…') }}
                                </p>

                                <a href="{{ route('competitions.show', $competition) }}"
                                   class="inline-block bg-blue-800 hover:bg-blue-900 text-white text-sm font-medium py-2 px-4 rounded">
                                    Czytaj dalej…
                                </a>
                            </div>
                        @endif
                    </article>
                @empty
                    <p class="text-gray-700">Brak nadchodzących wydarzeń.</p>
                @endforelse
            </section>

            {{-- Mini-kalendarz ---------------------------------------}}
            <section id="calendar" class="mt-12">

                {{-- Pasek tytułu + strzałki --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-3xl font-bold text-blue-900">Kalendarz ({{ $monthName }})</h3>
                    <div class="space-x-2">
                        <a href="{{ $prevUrl }}" class="px-3 py-1 rounded bg-[#eaf0f6] border border-[#cdd7e4] text-blue-900 hover:bg-[#d9e4f2]">&larr;</a>
                        <a href="{{ $nextUrl }}" class="px-3 py-1 rounded bg-[#eaf0f6] border border-[#cdd7e4] text-blue-900 hover:bg-[#d9e4f2]">&rarr;</a>
                    </div>
                </div>

                {{-- Siatka miesięczna --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-center select-none">
                        <thead>
                            <tr class="bg-[#002d62] text-white text-sm">
                                <th class="py-2 font-semibold">Pn</th><th class="py-2 font-semibold">Wt</th>
                                <th class="py-2 font-semibold">Śr</th><th class="py-2 font-semibold">Cz</th>
                                <th class="py-2 font-semibold">Pt</th><th class="py-2 font-semibold">Sb</th>
                                <th class="py-2 font-semibold">Nd</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @php
                                $day   = 1 - $offset;
                                $today = \Carbon\Carbon::today();
                            @endphp

                            @for($row = 0; $row < 6 && $day <= $daysInMonth; $row++)
                                <tr>
                                    @for($col = 0; $col < 7; $col++, $day++)
                                        @if($day < 1 || $day > $daysInMonth)
                                            <td class="h-20 w-32 border border-[#cdd7e4] bg-[#f9fbfd]"></td>
                                        @else
                                            @php
                                                $ev        = $eventsByDay[$day] ?? collect();
                                                $date      = \Carbon\Carbon::create($year, $month, $day);
                                                $isToday   = $date->isSameDay($today);
                                                $isWeekend = $col >= 5;
                                                $shown     = 0;
                                            @endphp
                                            <td class="h-20 w-32 align-top p-1 border border-[#cdd7e4]
                                                       {{ $isWeekend ? 'bg-[#f2f6fa]' : 'bg-[#f9fbfd]' }}
                                                       {{ $isToday ? 'ring-2 ring-blue-900/60' : '' }}">

                                                {{-- numer dnia --}}
                                                <div class="text-xs text-left {{ $isToday ? 'font-bold text-blue-900' : 'text-gray-700' }}">
                                                    {{ $day }}
                                                </div>

                                                {{-- każdy konkurs ⇒ własny pasek (max 3) --}}
                                                @foreach($ev as $event)
                                                    @break($shown === 3)
                                                    <div class="mt-1 text-[11px] leading-tight text-white bg-blue-900 rounded px-1 truncate"
                                                         title="{{ $event->name }}">
                                                        {{ \Illuminate\Support\Str::limit($event->name, 20) }}
                                                    </div>
                                                    @php $shown++ @endphp
                                                @endforeach

                                                {{-- +N jeśli więcej niż 3 --}}
                                                @if($ev->count() > $shown)
                                                    <div class="mt-1 text-[11px] leading-tight text-blue-900 bg-[#eaf0f6] rounded px-1">
                                                        +{{ $ev->count() - $shown }}
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <p class="text-sm text-gray-500 mt-2">
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
