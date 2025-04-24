<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Szczegóły konkursu</h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">
      {{-- dane konkursu --}}
      <h1 class="text-2xl font-bold mb-2">{{ $competition->name }}</h1>
      <p class="text-gray-700 mb-4">{{ $competition->description }}</p>
      <p class="text-sm text-gray-500 mb-4">Od: {{ $competition->start_date }} do: {{ $competition->end_date }}</p>

      {{-- komunikat sukcesu --}}
      @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
          {{ session('success') }}
        </div>
      @endif

      {{-- przycisk do formularza rejestracji --}}
      <a href="{{ route('competitions.showRegisterForm', $competition) }}"
         class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        Zarejestruj uczniów
      </a>
      {{-- Tabelka z zapisanymi uczniami --}}
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
          </tr>
        </thead>
        <tbody>
          @foreach($userRegistrations as $reg)
            @if($reg->student)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 border-b">{{ $reg->student->name }}</td>
                <td class="px-4 py-2 border-b">{{ $reg->student->last_name }}</td>
                <td class="px-4 py-2 border-b">{{ $reg->student->class }}</td>
                <td class="px-4 py-2 border-b">{{ $reg->student->school }}</td>
              </tr>
            @endif
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

      <a href="{{ route('competitions.index') }}"
         class="text-blue-500 hover:underline mt-4 block">
        ← Wróć do listy konkursów
      </a>
    </div>
  </div>
</x-app-layout>
