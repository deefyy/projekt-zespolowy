<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-[#002d62]">{{ __('Homepage') }}</h2>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen scroll-mt-20" id="start">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <section>
                <h3 class="text-3xl font-bold text-[#002d62] mb-6 border-b pb-2 border-[#cdd7e4]">{{ __('Upcoming events') }}</h3>

                @forelse($upcomingCompetitions as $competition)
                    @php
                        $startDate = \Carbon\Carbon::parse($competition->start_date);
                    @endphp

                    <article class="bg-white rounded-2xl shadow-md hover:shadow-lg transition mb-6 border border-[#cdd7e4] overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            @if($competition->poster_path)
                                <div class="md:w-[280px] w-full h-56 md:h-auto relative order-1 md:order-none">
                                    <img src="{{ Storage::url($competition->poster_path) }}"
                                         alt="{{ __('Poster for') }} {{ $competition->name }}"
                                         class="absolute inset-0 w-full h-full object-cover object-center rounded-t-2xl md:rounded-none md:rounded-l-2xl">
                                </div>
                            @endif
                            <div class="p-6 flex flex-col justify-between flex-1 order-0">
                                <div>
                                    <h4 class="text-2xl font-bold text-[#002d62] mb-1 break-words">
                                        {{ $competition->name }}
                                    </h4>
                                    <p class="text-gray-500 text-sm mb-2">
                                        📅 {{ $startDate->format('d.m.Y') }}
                                        @if($competition->end_date && $competition->end_date != $competition->start_date)
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
                                        {{ __('Read more...') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-gray-700">{{ __('No upcoming events.') }}</p>
                @endforelse
            </section>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-16">
                <div class="bg-white border border-[#cdd7e4] p-6 rounded-xl shadow-sm">
                    <h3 class="text-xl font-bold text-[#002d62] mb-2">{{ __('Welcome to the competition platform') }}</h3>
                    <p class="text-gray-700 text-sm leading-relaxed">
                        {{ __('Our platform allows you to browse upcoming events, register for competitions, follow the calendar, and learn detailed information about each event.') }}
                    </p>
                </div>
                <div class="bg-[#002d62] text-white p-6 rounded-xl shadow-sm">
                    <h3 class="text-xl font-bold mb-2">{{ __('Modern and accessible') }}</h3>
                    <p class="text-sm leading-relaxed">
                        {{ __('The service has been designed with accessibility and a modern look in mind. We care about readability, contrast, and user comfort on every device.') }}
                    </p>
                </div>
            </div>

            <section id="calendar" class="mt-12">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-3xl font-bold text-blue-900">{{ __('Calendar') }} ({{ $monthName }})</h3>
                    <div class="space-x-2">
                        <a href="{{ $prevUrl }}" class="px-3 py-1 rounded bg-[#eaf0f6] border border-[#cdd7e4] text-blue-900 hover:bg-[#d9e4f2]">←</a>
                        <a href="{{ $nextUrl }}" class="px-3 py-1 rounded bg-[#eaf0f6] border border-[#cdd7e4] text-blue-900 hover:bg-[#d9e4f2]">→</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-center select-none">
                        <thead>
                            <tr class="bg-[#002d62] text-white text-sm">
                                <th class="py-2 font-semibold">{{ __('Mon') }}</th>
                                <th class="py-2 font-semibold">{{ __('Tue') }}</th>
                                <th class="py-2 font-semibold">{{ __('Wed') }}</th>
                                <th class="py-2 font-semibold">{{ __('Thu') }}</th>
                                <th class="py-2 font-semibold">{{ __('Fri') }}</th>
                                <th class="py-2 font-semibold">{{ __('Sat') }}</th>
                                <th class="py-2 font-semibold">{{ __('Sun') }}</th>
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
                                                $ev        = $eventsByDay[(int)$day] ?? collect();
                                                $date      = \Carbon\Carbon::create($year, $month, $day);
                                                $isToday   = $date->isSameDay($today);
                                                $isWeekend = $col >= 5;
                                                $shown     = 0;
                                            @endphp

                                            <td class="h-20 w-32 align-top p-1 border border-[#cdd7e4]
                                                       {{ $isWeekend ? 'bg-[#f2f6fa]' : 'bg-[#f9fbfd]' }}
                                                       {{ $isToday ? 'ring-2 ring-blue-900/60' : '' }}">
                                                <div class="text-xs text-left {{ $isToday ? 'font-bold text-blue-900' : 'text-gray-700' }}">
                                                    {{ $day }}
                                                </div>
                                                @foreach($ev as $event)
                                                    @break($shown === 3)
                                                    <div class="mt-1 text-[11px] leading-tight text-white bg-blue-900 rounded px-1 truncate"
                                                         title="{{ $event->name }}">
                                                        {{ \Illuminate\Support\Str::limit($event->name, 20) }}
                                                    </div>
                                                    @php $shown++ @endphp
                                                @endforeach
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
            </section>
        </div>
    </div>
</x-app-layout>
