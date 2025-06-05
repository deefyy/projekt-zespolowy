<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Szczegóły konkursu</h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">
      {{-- dane konkursu --}}
      <h1 class="text-2xl font-bold mb-2 break-words">{{ $competition->name }}</h1>
      <p class="text-gray-700 mb-2 break-words">{{ $competition->description }}</p>

      {{-- ⬇︎  NOWOŚĆ – plakat, jeśli istnieje --}}
      @if($competition->poster_path)
        <div class="mb-4">
          <img src="{{ Storage::url($competition->poster_path) }}"
               alt="Plakat konkursu {{ $competition->name }}"
               class="w-full max-h-96 object-contain rounded-lg shadow-md">
        </div>
      @endif

      <p class="text-sm text-gray-500 mb-2">
        Liczba etapów: {{ $competition->stages_count }}
      </p>
      <p class="text-sm text-gray-500 mb-2">
        Od: {{ $competition->start_date }} do: {{ $competition->end_date }}
      </p>
      <p class="text-sm text-gray-500 mb-4">
        Zapisy do: {{ $competition->registration_deadline }}
      </p>

      {{-- komunikaty --}}
      @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
          {{ session('success') }}
        </div>
      @elseif(session('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
          {{ session('error') }}
        </div>
      @endif

      {{-- przycisk rejestracji --}}
      @auth
      <div class="flex gap-2">
        @if(now()->lessThanOrEqualTo($competition->registration_deadline))
          <a href="{{ route('competitions.showRegisterForm', $competition) }}"
             class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Zarejestruj uczniów
          </a>
          <a href="{{ route('competitions.showImportRegistrationsForm', $competition) }}"
             class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">
            Importuj z Excela
          </a>
        @else
          <p class="text-red-600 font-semibold">Rejestracja została zakończona.</p>
        @endif
      </div>
      @endauth

      @guest
        <p class="text-red-600 mt-4 font-semibold">
          <a href="{{ route('login') }}" class="underline">Zaloguj się</a>, aby zapisać uczniów na konkurs.
        </p>
      @endguest

        @php
            $user     = auth()->user();
            $isAdmin  = $user?->role === 'admin';
            $isOwner  = $competition->user_id === $user?->id;
        @endphp

        @if($isAdmin || $isOwner)
        <div class="mt-2 flex gap-2">
          <a href="{{ route('competitions.edit', $competition) }}"
             class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
            Edytuj konkurs
          </a>
          <form action="{{ route('competitions.destroy', $competition) }}" method="POST"
                onsubmit="return confirm('Czy na pewno chcesz usunąć ten konkurs?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" data-skip-lock>
              Usuń konkurs
            </button>
          </form>
          <a href="{{ route('competitions.exportRegistrations', $competition) }}"
             class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">
            Eksportuj do Excela
          </a>
          <a href="{{ route('competitions.points.edit', $competition) }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            Zarządzaj punktami
        </a>
        </div>
      @endif
    

      {{-- tabela z uczniami --}}
          @auth
              @if($userRegistrations->count() > 0)
                  <div class="mt-8">
                      <h3 class="text-lg font-semibold mb-4">Twoi zapisani uczniowie:</h3>

                      <div class="overflow-x-auto">
                          <table class="min-w-full bg-white border border-gray-200 rounded">
                              <thead class="bg-gray-100">
                                  <tr>
                                      <th class="text-left px-4 py-2 border-b">Imię</th>
                                      <th class="text-left px-4 py-2 border-b">Nazwisko</th>
                                      <th class="text-left px-4 py-2 border-b">Klasa</th>
                                      <th class="text-left px-4 py-2 border-b">Szkoła</th>

                                      {{-- dynamiczne kolumny etapów --}}
                                      @foreach($competition->stages as $stage)
                                        <th class="text-center px-4 py-2 border-b">
                                          Etap {{ $stage->stage }}
                                        </th>
                                      @endforeach

                                      <th class="text-left px-4 py-2 border-b">Akcje</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  @php
                                      $registrationStillOpen = now()->lessThanOrEqualTo($competition->registration_deadline);
                                      $user     = auth()->user();
                                      $isAdmin  = $user?->role === 'admin';
                                      $isOwner  = $competition->user_id === $user?->id
                                  @endphp

                                  @foreach($userRegistrations as $reg)
                                      @if($reg->student)
                                          @php
                                              $canEditOrDelete = $isAdmin || $isOwner ||  (
                                                  $reg->user_id === auth()->id() && $registrationStillOpen
                                              );
                                          @endphp
                                          <tr class="hover:bg-gray-50">
                                              <td class="px-4 py-2 border-b">{{ $reg->student->name }}</td>
                                              <td class="px-4 py-2 border-b">{{ $reg->student->last_name }}</td>
                                              <td class="px-4 py-2 border-b">{{ $reg->student->class }}</td>
                                              <td class="px-4 py-2 border-b">{{ $reg->student->school }}</td>

                                              @foreach($competition->stages as $stage)
                                                @php
                                                  // znajdź rekord pivot dla tego ucznia & etapu
                                                  $sc = $reg->student->stageCompetitions->first(fn($sc) => $sc->stage_id === $stage->id);
                                                @endphp
                                                <td class="px-4 py-2 border-b text-center">
                                                  {{ $sc->result ?? '-' }}
                                                </td>
                                              @endforeach

                                              <td class="px-4 py-2 border-b">
                                                  @if($canEditOrDelete)
                                                      <a href="{{ route('students.edit', $reg->student->id) }}"
                                                        class="text-blue-500 hover:underline mr-2">Edytuj</a>

                                                      <form action="{{ route('students.destroy', $reg->student->id) }}"
                                                            method="POST" class="inline-block"
                                                            onsubmit="return confirm('Na pewno chcesz usunąć ucznia?');">
                                                          @csrf
                                                          @method('DELETE')
                                                          <button type="submit" class="text-red-500 hover:underline" data-skip-lock>Usuń</button>
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
                      {{-- selektor liczby wierszy na stronę --}}
                      <form method="GET" class="mb-4">
                          {{-- zachowuj inne parametry wyszukiwania, jeśli kiedyś dodasz --}}
                          @foreach (request()->except('perPage', 'page') as $key => $value)
                              <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                          @endforeach

                          <label class="text-sm font-medium text-gray-700">
                              Pokaż
                              <select name="perPage" class="border-gray-300 rounded mx-1"
                                      onchange="this.form.submit()">
                                  @foreach([10, 20, 50, 100] as $size)
                                      <option value="{{ $size }}" {{ request('perPage', 10) == $size ? 'selected' : '' }}>
                                          {{ $size }}
                                      </option>
                                  @endforeach
                              </select>
                              wpisów na stronę
                          </label>
                      </form>
                      <div class="mt-4">
                        {{ $userRegistrations->links() }}
                      </div>
                  </div>
              @endif
          @endauth

      {{-- powrót --}}
      <a href="{{ route('competitions.index') }}"
         class="text-blue-500 hover:underline mt-4 block">
        ← Wróć do listy konkursów
      </a>
    </div>
  </div>
</x-app-layout>
