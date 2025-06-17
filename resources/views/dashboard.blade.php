<x-app-layout>
    <!-- NAGŁÓWEK -->
     

    <x-slot name="header">
        <h2 class="font-bold text-2xl text-[#002d62]">Strona Główna</h2>
    </x-slot>

    <!-- GŁÓWNA ZAWARTOŚĆ -->
    <div class="py-12 bg-[#f9fbfd] min-h-screen scroll-mt-20" id="start">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- NAJBLIŻSZE WYDARZENIA -->
            <section>
                <h3 class="text-3xl font-bold text-[#002d62] mb-6 border-b pb-2 border-[#cdd7e4]">Najbliższe wydarzenia</h3>

                @forelse($upcomingCompetitions as $competition)
    @php
        $startDate = \Carbon\Carbon::parse($competition->start_date);
        $daysLeft = now()->diffInDays($startDate, false);
    @endphp

    <article class="bg-white rounded-2xl shadow-md hover:shadow-lg transition mb-6 border border-[#cdd7e4] overflow-hidden">
        <div class="flex flex-col md:flex-row">

            {{-- OBRAZEK PO LEWEJ --}}
            @if($competition->poster_path)
                <div class="md:w-[280px] w-full h-56 md:h-auto relative order-1 md:order-none">
                    <img src="{{ Storage::url($competition->poster_path) }}"
                         alt="Plakat {{ $competition->name }}"
                         class="absolute inset-0 w-full h-full object-cover object-center rounded-t-2xl md:rounded-none md:rounded-l-2xl">
                </div>
            @endif

            {{-- TREŚĆ PO PRAWEJ --}}
            <div class="p-6 flex flex-col justify-between flex-1 order-0">
                <div>
                    <h4 class="text-2xl font-bold text-[#002d62] mb-1 break-words">
                        {{ $competition->name }}
                    </h4>

                    <p class="text-gray-500 text-sm mb-2">
                        📅 {{ $startDate->format('d.m.Y') }}
                        @if($competition->end_date)
                            – {{ \Carbon\Carbon::parse($competition->end_date)->format('d.m.Y') }}
                        @endif
                    </p>

                    <p class="text-gray-700 mb-2 line-clamp-3">
                        {{ \Illuminate\Support\Str::limit(strip_tags($competition->description), 120, '…') }}
                    </p>
                </div>

                <div class="mt-4">
                    

                    <a href="{{ route('competitions.show', $competition) }}"
                       class="inline-block bg-[#002d62] hover:bg-[#00193c] text-white text-sm font-medium py-2 px-4 rounded-xl">
                        Czytaj dalej…
                    </a>
                </div>
            </div>
        </div>
    </article>
@empty
    <p class="text-gray-700">Brak nadchodzących wydarzeń.</p>
@endforelse


            </section>

            <!-- WSTĘP INFORMACYJNY -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-16">
                <div class="bg-white border border-[#cdd7e4] p-6 rounded-xl shadow-sm">
                    <h3 class="text-xl font-bold text-[#002d62] mb-2">Witamy na platformie konkursowej</h3>
                    <p class="text-gray-700 text-sm leading-relaxed">
                        Nasza platforma umożliwia przeglądanie nadchodzących wydarzeń, rejestrowanie się na konkursy,
                        śledzenie kalendarza oraz poznawanie szczegółowych informacji o każdym wydarzeniu.
                    </p>
                </div>
                <div class="bg-[#002d62] text-white p-6 rounded-xl shadow-sm">
                    <h3 class="text-xl font-bold mb-2">Nowoczesna i dostępna</h3>
                    <p class="text-sm leading-relaxed">
                        Serwis został zaprojektowany z myślą o dostępności i nowoczesnym wyglądzie. 
                        Dbamy o czytelność, kontrast i wygodę użytkowania na każdym urządzeniu.
                    </p>
                </div>
            </div>

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
                                                $ev        = $eventsByDay[(int)$day] ?? collect(); // 👈 najważniejsza zmiana
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
