@php
    $currentSort = request('sort');
    $currentDirection = request('direction') ?? 'asc';

    function sortIcon($column) {
        $sort = request('sort');
        $direction = request('direction', 'asc');
        return $sort === $column ? ($direction === 'asc' ? '▲' : '▼') : '↕';
    }

    function sortLink($column, $label) {
        $currentSort = request('sort');
        $currentDirection = request('direction') ?? 'asc';
        $nextDirection = ($currentSort === $column && $currentDirection === 'asc') ? 'desc' : 'asc';
        $search = request('search');
        $perPage = request('perPage', 10);
        $url = url()->current() . "?sort={$column}&direction={$nextDirection}&search={$search}&perPage={$perPage}";
        return "<a href=\"{$url}\" class=\"hover:underline\">{$label} <span class=\"sort-icon\">" . sortIcon($column) . "</span></a>";
    }

    $user = auth()->user();
    $isAdmin = $user?->role === 'admin';
    $isOwner = $user?->id && $competition->user_id === $user->id;
    $isCoOrganizer = $user?->id && $competition->coOrganizers()->where('user_id', $user->id)->exists();
    $showPanel = $isAdmin || $isOwner || $isCoOrganizer;
@endphp

<x-app-layout>
  <x-slot name="header">
    <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-[#002d62] text-center">Szczegóły konkursu</h2>
      </div>
    </header>
  </x-slot>

  <div class="py-10 bg-[#f9fbfd]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid {{ $showPanel ? 'md:grid-cols-4' : 'grid-cols-1' }} gap-6 justify-center">
        <div class="{{ $showPanel ? 'md:col-span-3' : 'max-w-3xl mx-auto' }} bg-white p-6 rounded-xl shadow space-y-4">
          <h1 class="text-2xl font-bold text-[#002d62] break-words">{{ $competition->name }}</h1>
          <p class="text-gray-700 break-words">{{ $competition->description }}</p>

          @if($competition->poster_path)
            <div>
              <img src="{{ Storage::url($competition->poster_path) }}" alt="Plakat konkursu {{ $competition->name }}" class="w-full max-h-96 object-contain rounded-xl shadow">
            </div>
          @endif

          <p class="text-sm text-gray-500">Liczba etapów: {{ $competition->stages_count }}</p>
          <p class="text-sm text-gray-500">Od: {{ $competition->start_date }} do: {{ $competition->end_date }}</p>
          <p class="text-sm text-gray-500 mb-4">Zapisy do: {{ $competition->registration_deadline }}</p>

          @if(session('success'))
            <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
          @elseif(session('error'))
            <div class="p-4 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
          @endif

          @auth
            @if(now()->lessThanOrEqualTo($competition->registration_deadline))
              <a href="{{ route('competitions.showRegisterForm', $competition) }}" class="inline-block bg-[#002d62] text-white px-4 py-2 rounded-xl hover:bg-[#001b3e]">Zarejestruj uczniów</a>
            @else
              <p class="text-red-600 font-semibold">Rejestracja została zakończona.</p>
            @endif
          @endauth

          @guest
            <p class="text-red-600 font-semibold">
              <a href="{{ route('login') }}" class="underline">Zaloguj się</a>, aby zapisać uczniów na konkurs.
            </p>
          @endguest
        </div>

        @if($showPanel)
<div class="bg-[#eaf0f6] border border-[#cdd7e4] p-4 rounded-xl space-y-3 h-fit">
    <h3 class="text-lg font-bold text-[#002d62] mb-3">Zarządzanie konkursem</h3>

    <a href="{{ route('competitions.edit', $competition) }}" class="block bg-yellow-500 text-white text-center py-2 px-3 rounded hover:bg-yellow-600">Edytuj konkurs</a>

    @if(!$isCoOrganizer)
        <!-- Przycisk otwierający modal -->
<button type="button"
        onclick="document.getElementById('delete-modal').classList.remove('hidden')"
        class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
    Usuń konkurs
</button>

<!-- MODAL -->
<div id="delete-modal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <form action="{{ route('competitions.destroy', $competition) }}" method="POST">
            @csrf
            @method('DELETE')
            <h3 class="text-lg font-bold mb-2">Potwierdzenie usunięcia</h3>
            <p>Czy na pewno chcesz usunąć konkurs <strong>{{ $competition->name }}</strong>?</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('delete-modal').classList.add('hidden')"
                        class="px-4 py-2 border rounded hover:bg-gray-100">
                    Anuluj
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    Usuń
                </button>
            </div>
        </form>
    </div>
</div>

    @endif

    <a href="{{ route('competitions.exportRegistrations', $competition) }}" class="block bg-green-700 text-white text-center py-2 px-3 rounded hover:bg-green-800">Eksportuj do Excela</a>
    <a href="{{ route('competitions.showImportForm', $competition) }}" class="block bg-green-700 text-white text-center py-2 px-3 rounded hover:bg-green-800">Importuj z Excela</a>
    <a href="{{ route('competitions.points.edit', $competition) }}" class="block bg-indigo-600 text-white text-center py-2 px-3 rounded hover:bg-indigo-700">Zarządzaj punktami</a>

    @if(!$isCoOrganizer)
    <form action="{{ route('competitions.inviteCoorganizer', $competition) }}" method="POST" class="mt-4">
        @csrf
        <input type="email" name="email" required placeholder="Email współorganizatora" class="form-input border border-gray-300 rounded px-3 py-2 w-full mb-2" />
        <button type="submit" class="w-full bg-[#002d62] text-white py-2 rounded hover:bg-[#001b3c]">Dodaj współorganizatora</button>
    </form>
    @endif
</div>
@endif

      </div>

      @auth
      @if($userRegistrations->count() > 0)
      <div class="mt-12 bg-white p-6 rounded-xl shadow">
        <h3 class="text-xl font-bold text-[#002d62] mb-4">Twoi zapisani uczniowie:</h3>

        <form method="GET" class="mb-4 flex flex-wrap gap-4 items-end">
          @foreach (request()->except('search', 'page') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
          @endforeach

          <div>
            <label for="search" class="block text-sm font-medium text-gray-700">Szukaj ucznia</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" class="border-gray-300 rounded w-48" placeholder="Imię, nazwisko, klasa, szkoła..." />
          </div>

          <div>
            <button type="submit" class="bg-[#002d62] text-white px-4 py-2 rounded hover:bg-[#001b3c] mt-5">Szukaj</button>
          </div>
        </form>

        <div class="overflow-x-auto">
          <table class="min-w-full bg-white border border-gray-200 rounded">
            <thead class="bg-gray-100">
              <tr>
                <th class="text-left px-4 py-2 border-b">{!! sortLink('student.name', 'Imię') !!}</th>
                <th class="text-left px-4 py-2 border-b">{!! sortLink('student.last_name', 'Nazwisko') !!}</th>
                <th class="text-left px-4 py-2 border-b">{!! sortLink('student.class', 'Klasa') !!}</th>
                <th class="text-left px-4 py-2 border-b">{!! sortLink('student.school', 'Szkoła') !!}</th>
                @foreach($competition->stages as $stage)
                  <th class="text-center px-4 py-2 border-b">Etap {{ $stage->stage }}</th>
                @endforeach
                <th class="text-left px-4 py-2 border-b">Akcje</th>
              </tr>
            </thead>
            <tbody>
              @foreach($userRegistrations as $reg)
                @if($reg->student)
                  @php
                    $canEditOrDelete = $isAdmin || $isOwner || $isCoOrganizer || (
                      $reg->user_id === auth()->id() && now()->lessThanOrEqualTo($competition->registration_deadline)
                    );
                  @endphp
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 border-b">{{ $reg->student->name }}</td>
                    <td class="px-4 py-2 border-b">{{ $reg->student->last_name }}</td>
                    <td class="px-4 py-2 border-b">{{ $reg->student->class }}</td>
                    <td class="px-4 py-2 border-b">{{ $reg->student->school }}</td>
                    @foreach($competition->stages as $stage)
                      @php
                        $sc = $reg->student->stageCompetitions->first(fn($sc) => $sc->stage_id === $stage->id);
                      @endphp
                      <td class="px-4 py-2 border-b text-center">{{ $sc->result ?? '-' }}</td>
                    @endforeach
                    <td class="px-4 py-2 border-b">
                      @if($canEditOrDelete)
                        <a href="{{ route('students.edit', $reg->student->id) }}" class="text-blue-500 hover:underline mr-2">Edytuj</a>
                        <form action="{{ route('students.destroy', $reg->student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Na pewno chcesz usunąć ucznia?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="text-red-500 hover:underline">Usuń</button>
                        </form>
                      @else
                        <span class="text-gray-400 italic">Brak dostępu</span>
                      @endif
                    </td>
                  </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>

        <form method="GET" class="my-4">
          @foreach (request()->except('perPage', 'page') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
          @endforeach
          <label class="text-sm font-medium text-gray-700">
            Pokaż
            <select name="perPage" class="border-gray-300 rounded mx-1" onchange="this.form.submit()">
              @foreach([10, 20, 50, 100] as $size)
                <option value="{{ $size }}" {{ request('perPage', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
              @endforeach
            </select>
            wpisów na stronę
          </label>
        </form>

        <div class="mt-4">{{ $userRegistrations->links() }}</div>
      </div>
      @endif
      @endauth

      <a href="{{ route('competitions.index') }}" class="text-blue-500 hover:underline block mt-6">← Wróć do listy konkursów</a>
    </div>
  </div>
</x-app-layout>