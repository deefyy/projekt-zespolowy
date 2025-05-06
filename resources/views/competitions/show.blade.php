<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Szczegóły konkursu</h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">
      {{-- dane konkursu --}}
      <h1 class="text-2xl font-bold mb-2">{{ $competition->name }}</h1>
      <p class="text-gray-700 mb-4">{{ $competition->description }}</p>
      <p class="text-sm text-gray-500 mb-2">Od: {{ $competition->start_date }} do: {{ $competition->end_date }}</p>
      <p class="text-sm text-gray-500 mb-4">Zapisy do: {{ $competition->registration_deadline }}</p>

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
      @if(now()->lessThanOrEqualTo($competition->registration_deadline))
        <a href="{{ route('competitions.showRegisterForm', $competition) }}"
           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Zarejestruj uczniów
        </a>
      @else
        <p class="text-red-600 font-semibold">Rejestracja została zakończona.</p>
      @endif

      {{-- przyciski admina --}}
      @if(auth()->user()?->role === 'admin')
        <div class="mt-4 flex gap-2">
          <a href="{{ route('competitions.edit', $competition) }}"
             class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
            Edytuj konkurs
          </a>

          <form action="{{ route('competitions.destroy', $competition) }}" method="POST"
                onsubmit="return confirm('Czy na pewno chcesz usunąć ten konkurs?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
              Usuń konkurs
            </button>
          </form>
        </div>
      @endif

      {{-- tabela z uczniami --}}
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
                  <th class="text-left px-4 py-2 border-b">Akcje</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $registrationStillOpen = now()->lessThanOrEqualTo($competition->registration_deadline);
                  $isAdmin = auth()->user()?->role === 'admin';
                @endphp

                @foreach($userRegistrations as $reg)
                  @if($reg->student)
                    @php
                      $canEditOrDelete = $isAdmin || (
                        $reg->user_id === auth()->id() && $registrationStillOpen
                      );
                    @endphp
                    <tr class="hover:bg-gray-50">
                      <td class="px-4 py-2 border-b">{{ $reg->student->name }}</td>
                      <td class="px-4 py-2 border-b">{{ $reg->student->last_name }}</td>
                      <td class="px-4 py-2 border-b">{{ $reg->student->class }}</td>
                      <td class="px-4 py-2 border-b">{{ $reg->student->school }}</td>
                      <td class="px-4 py-2 border-b">
                        @if($canEditOrDelete)
                          <a href="{{ route('students.edit', $reg->student->id) }}"
                             class="text-blue-500 hover:underline mr-2">Edytuj</a>

                          <form action="{{ route('students.destroy', $reg->student->id) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Na pewno chcesz usunąć ucznia?');">
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
        </div>
      @endif

      {{-- powrót --}}
      <a href="{{ route('competitions.index') }}"
         class="text-blue-500 hover:underline mt-4 block">
        ← Wróć do listy konkursów
      </a>
    </div>
  </div>
</x-app-layout>
