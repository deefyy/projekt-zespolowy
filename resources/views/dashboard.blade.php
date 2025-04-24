<x-app-layout>
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Sekcja najbliższych konkursów --}}
        <div class="bg-white p-6 rounded shadow mb-6">
            <h3 class="text-xl font-bold mb-4">Najbliższe konkursy</h3>
            @if($upcomingCompetitions->count())
                <ul class="list-disc ml-6">
                    @foreach ($upcomingCompetitions as $competition)
                        <li>
                            <a href="{{ route('competitions.show', $competition) }}" class="text-blue-600 hover:underline">
                                {{ $competition->name }}
                            </a>
                            <span class="text-gray-500 text-sm">
                                ({{ \Carbon\Carbon::parse($competition->start_date)->format('d.m.Y') }})
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-600">Brak nadchodzących konkursów.</p>
            @endif
        </div>

        {{-- Sekcja kalendarza --}}
        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-xl font-bold mb-4">Kalendarz konkursów</h3>
            <div id='calendar'></div>
        </div>
    </div>
</div>

{{-- FullCalendar CSS & JS --}}
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>

{{-- Kalendarz --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'pl',
            events: @json($calendarEvents),
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listWeek'
            }
        });

        calendar.render();
    });
</script>

{{-- Styl kalendarza --}}
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }
</style>

</x-app-layout>
